<?php
namespace App\Service;

use Twilio\Rest\Client;

class SmsSender
{
    private $twilio;

    public function __construct(string $accountSid, string $authToken)
    {
        $this->twilio = new Client($accountSid, $authToken);
    }

    public function sendSms(string $to, string $message): void
    {
        $this->twilio->messages->create($to, [
            'from' => '+17752589009',
            'body' => $message
        ]);
    }
}
?>