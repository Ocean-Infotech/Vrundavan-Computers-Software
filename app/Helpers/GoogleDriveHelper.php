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
        $client->setAuthConfig(storage_path('google/vrundavan-computer-469909-c5332d639c01.json'));
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
   public static function createFolder($folderName, $parentFolderId)
{
    $client = self::getClient();
    $service = new \Google_Service_Drive($client);

    $fileMetadata = new \Google_Service_Drive_DriveFile([
        'name'     => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents'  => [$parentFolderId],
    ]);

    $folder = $service->files->create($fileMetadata, ['fields' => 'id, name, webViewLink']);

    try {
        $permission = new \Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'writer',
        ]);
        $service->permissions->create($folder->id, $permission);
    } catch (\Exception $e) {
        \Log::error("Failed to set Google Drive permission on folder " . $folder->id . ": " . $e->getMessage());
    }

    return [
        'id'   => $folder->id,
        'name' => $folder->name,
        'link' => $folder->webViewLink,
    ];
}

public static function shareFolder($folderId)
{
    $client = self::getClient();
    $service = new \Google_Service_Drive($client);
    $permission = new \Google_Service_Drive_Permission([
        'type' => 'anyone',
        'role' => 'writer',
    ]);
    return $service->permissions->create($folderId, $permission);
}

}
