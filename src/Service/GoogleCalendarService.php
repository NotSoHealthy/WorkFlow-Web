<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Reservation;
use DateTime;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Event as GoogleEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleCalendarService
{
    private GoogleClient $client;
    private ParameterBagInterface $parameterBag;
    private UrlGeneratorInterface $urlGenerator;
    private SessionInterface $session;

    public function __construct(
        ParameterBagInterface $parameterBag,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        $this->parameterBag = $parameterBag;
        $this->urlGenerator = $urlGenerator;
        $this->session = $requestStack->getSession();
        
        $this->client = new GoogleClient();
        $this->client->setApplicationName('Your Application Name');
        $this->client->setScopes([
            Calendar::CALENDAR_EVENTS,
            Calendar::CALENDAR
        ]);
        
        // Set the path to your credentials JSON file
        $credentialsPath = $this->parameterBag->get('kernel.project_dir') . '/config/google_credentials.json';
        $this->client->setAuthConfig($credentialsPath);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent'); // Force re-consent to get refresh token
        
        // Load previously authorized token from session, if it exists
        $token = $this->session->get('google_access_token');
        if ($token) {
            $this->client->setAccessToken($token);
        }
    }

    public function getAuthUrl(int $reservationId): string
    {
        // Store the reservation ID in the session for later use
        $this->session->set('reservation_id', $reservationId);
        
        // Generate the callback URL
        $redirectUri = $this->urlGenerator->generate(
            'app_google_calendar_callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $this->client->setRedirectUri($redirectUri);
        
        // Get authorization URL
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $authCode): bool
    {
        $redirectUri = $this->urlGenerator->generate(
            'app_google_calendar_callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $this->client->setRedirectUri($redirectUri);
        
        try {
            // Exchange authorization code for an access token
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            $this->client->setAccessToken($accessToken);
            
            // Save the token to the session
            $this->session->set('google_access_token', $accessToken);
            
            // Check for errors
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(join(', ', $accessToken));
            }
            
            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Google Calendar API Error: ' . $e->getMessage());
            return false;
        }
    }

    public function addEventToCalendar(Reservation $reservation): array
{
    // Check if we have a valid access token
    if (!$this->client->isAccessTokenExpired()) {
        try {
            $service = new Calendar($this->client);
            $event = $reservation->getEvent();
            
            // Create Google Calendar event
            $googleEvent = new GoogleEvent();
            $googleEvent->setSummary($event->getTitle());
            $googleEvent->setDescription($event->getDescription() . 
                "\n\nReservation Details:" .
                "\nType: " . $reservation->getType() .
                "\nNumber of Places: " . $reservation->getNombreDePlaces() .
                "\nTotal Price: " . $reservation->getPrice() . "€");
            
            // Set location if available
            if ($event->getLocation()) {
                $googleEvent->setLocation($event->getLocation());
            }
            
            // Set start time
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($event->getDateAndTime()->format('c'));
            $googleEvent->setStart($startDateTime);
            
            // Set end time (3 hours after start time)
            $endDateTime = new EventDateTime();
            $endDate = clone $event->getDateAndTime();
            /** @var DateTime $endDate */
            $endDate->modify('+3 hours');
            $endDateTime->setDateTime($endDate->format('c'));
            $googleEvent->setEnd($endDateTime);
            
            // Add reminders
            $reminders = new \Google\Service\Calendar\EventReminders();
            $reminders->setUseDefault(false);
            
            $reminder = new \Google\Service\Calendar\EventReminder();
            $reminder->setMethod('popup');
            $reminder->setMinutes(60); // 1 hour before
            
            $reminders->setOverrides([$reminder]);
            $googleEvent->setReminders($reminders);
            
            // If the event is online, add Google Meet conferencing
            $meetLink = null;
            if ($event->isOnline()) {
                $conferenceData = new \Google\Service\Calendar\ConferenceData();
                $conferenceRequest = new \Google\Service\Calendar\CreateConferenceRequest();
                $conferenceRequest->setRequestId(uniqid());
                $conferenceData->setCreateRequest($conferenceRequest);
                $googleEvent->setConferenceData($conferenceData);
                
                // Insert event to the user's primary calendar with conferencing
                $calendarId = 'primary';
                $optParams = ['conferenceDataVersion' => 1];
                $createdEvent = $service->events->insert($calendarId, $googleEvent, $optParams);
                
                // Extract the Meet link if available
                if ($createdEvent->getConferenceData() && 
                    $createdEvent->getConferenceData()->getEntryPoints()) {
                    foreach ($createdEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                        if ($entryPoint->getEntryPointType() === 'video') {
                            $meetLink = $entryPoint->getUri();
                            break;
                        }
                    }
                }
            } else {
                // Insert regular event without conferencing
                $calendarId = 'primary';
                $createdEvent = $service->events->insert($calendarId, $googleEvent);
            }
            
            return [
                'success' => true,
                'meetLink' => $meetLink,
                'eventId' => $createdEvent->getId()
            ];
        } catch (\Exception $e) {
            // Log the error
            error_log('Failed to add event to Google Calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'Access token expired'
    ];
}

    public function refreshTokenIfNeeded(): bool
    {
        if ($this->client->isAccessTokenExpired()) {
            // Try to refresh the token
            $refreshToken = $this->client->getRefreshToken();
            if ($refreshToken) {
                try {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $this->session->set('google_access_token', $this->client->getAccessToken());
                    return true;
                } catch (\Exception $e) {
                    // Log the error
                    error_log('Failed to refresh token: ' . $e->getMessage());
                    // Clear the invalid token
                    $this->session->remove('google_access_token');
                    return false;
                }
            } else {
                // No refresh token available
                $this->session->remove('google_access_token');
                return false;
            }
        }
        
        return true;
    }
}