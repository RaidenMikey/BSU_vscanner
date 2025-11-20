<?php
require_once __DIR__ . '/vendor/autoload.php';

function uploadToDrive($localPath, $fileName) {
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/config/service-account.json');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);

    $service = new Google_Service_Drive($client);

    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => $fileName,
        'parents' => ['1mRTcYZOZ34_5V57aD3Xf3cvvp5H26MoL'] // replace with your Google Drive folder ID
    ]);

    $content = file_get_contents($localPath);

    $file = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => mime_content_type($localPath),
        'uploadType' => 'multipart'
    ]);

    return $file->id; // Google Drive file ID
}
