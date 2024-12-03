<?php

// app/Services/FirebaseService.php

namespace App\Services;

use Google_Client;
use GuzzleHttp\Client;

class FirebaseService
{
    public function getAccessToken()
    {
        $credentialsPath = storage_path('firebase/credentials.json');
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        $client = new Google_Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope($scopes);

        $accessToken = $client->fetchAccessTokenWithAssertion();

        return $accessToken['access_token'] ?? null;
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            \Log::error('Failed to get access token.');
            return false;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/mlt-database/messages:send';

        $data = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ]
            ]
        ];

        $client = new Client();
        try {
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            \Log::error('Error sending FCM message: ' . $e->getMessage());
            return false;
        }
    }
}
