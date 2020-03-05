<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/class/helper.php';

function isCLI() {
    return php_sapi_name() == 'cli';
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();

    $client->setApplicationName('Google Drive API PHP Test');
    $client->setScopes(Google_Service_Drive::DRIVE_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setApprovalPrompt('consent');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $code = Arr::get('code', $_GET);

            if ($code) {
                $authCode = trim($code);
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
            } else {
                if (isCLI()) {
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);
                } else {
                    $redirect_uri = URL::get(true);
                    $client->setRedirectUri($redirect_uri);
                    $authUrl = $client->createAuthUrl();
                    echo "<a href='$authUrl'>Log in GoogleDrive</a>";
                    die();
                }
            }

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

// Get the API client and construct the service object.
$client = getClient();
isCLI() ? die() : '';

$service = new Google_Service_Drive($client);

// Options for file
$optParams = array(
    'pageSize' => 10,
    'orderBy' => 'createdTime desc',
    'q' => "trashed = false and mimeType != 'application/vnd.google-apps.folder' and 'gusev.demon2390@gmail.com' in owners",
    'fields' => 'files(id, name, thumbnailLink, createdTime, size, exportLinks, mimeType)'
);
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) {
    print "No files found.\n";
} else {
    include_once "form.php";
}
