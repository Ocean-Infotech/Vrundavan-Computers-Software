<?php

namespace App\Helpers;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDriveHelper
{
    protected static function getClient()
    {
        $client = new Google_Client();
        // JSON file path
        $client->setAuthConfig(storage_path('google/service-account.json'));
        $client->addScope(Google_Service_Drive::DRIVE);
        return $client;
    }

    /**
     * Create folder in Google Drive
     *
     * @param string $folderName
     * @param string $parentFolderId
     * @return array
     */
    public static function createFolder($folderName, $parentFolderId = null)
    {
        $client = self::getClient();
        $service = new Google_Service_Drive($client);

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentFolderId ? [$parentFolderId] : []
        ]);

        $folder = $service->files->create($fileMetadata, [
            'fields' => 'id, webViewLink'
        ]);

        return [
            'id' => $folder->id,
            'link' => $folder->webViewLink
        ];
    }
}
