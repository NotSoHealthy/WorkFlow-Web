<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Google\Client;
use Google\Service\Drive;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleDriveController extends AbstractController
{
    #[Route('/google-drive-auth/{reclamation_id}', name: 'google_drive_auth')]
    public function auth(Request $request, EntityManagerInterface $entityManager, $reclamation_id): Response
    {
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamation_id);
    
        if (!$reclamation || !$reclamation->getAttachedfile()) {
            $this->addFlash('error', 'No file found to upload');
            return $this->redirectToRoute('app_reclamation_index');
        }
    
     
        $credentialsPath = $this->getParameter('kernel.project_dir').'/config/google_credentials_drive.json';
        
      
        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE_FILE);
        
       
        $redirectUri = $this->generateUrl('google_drive_auth', ['reclamation_id' => $reclamation_id], UrlGeneratorInterface::ABSOLUTE_URL);
        $client->setRedirectUri($redirectUri);
        
      
        if ($code = $request->query->get('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            $client->setAccessToken($token);
            
            
            $driveService = new Drive($client);
            $fileMetadata = new Drive\DriveFile([
                'name' => $reclamation->getAttachedfile(),
                'parents' => ['root'] 
            ]);
            
            $filePath = $this->getParameter('kernel.project_dir').'/public/Uploads/applications/'.$reclamation->getAttachedfile();
            $content = file_get_contents($filePath);
            
            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink'
            ]);
            
            $this->addFlash('success', 'File uploaded to Google Drive: '.$file->getName());
            return $this->redirectToRoute('app_reclamation_index');
        } else {
            $authUrl = $client->createAuthUrl();
            return $this->redirect($authUrl);
        }
    }
}