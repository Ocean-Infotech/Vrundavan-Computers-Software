<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Client;

use Illuminate\Support\Facades\Storage;


class GoogleDriveController extends Controller
{
    public function createFolder(Request $request)
    {
        $folderName = $request->input('name', $request->foldername);

        $client = new Google_Client();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->refreshToken(config('filesystems.disks.google.refreshToken'));

        $service = new Drive($client);

        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        $folder = $service->files->create($fileMetadata, [
            'fields' => 'id, name'
        ]);

        $folderId = $folder->id;
        $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

        return response()->json([
            'folder_id' => $folderId,
            'folder_name' => $folder->name,
            'folder_url' => $folderUrl
        ]);
    }
}
