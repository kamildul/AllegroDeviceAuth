<?php

    namespace KamilDul\CategorySynchro\Allegro\AuthManager;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;
    use KamilDul\CategorySynchro\Allegro\AuthData\AuthData;

    class AuthManager
    {
        private const BASE_URI = 'https://allegro.pl';
        private const DEVICE_CODE_URL = '/auth/oauth/device';
        private const TOKEN_URL = '/auth/oauth/token';
        private const CONFIG_FILE = 'config/auth_config.json';

        private Client $client;
        private string $clientId;
        private string $clientSecret;
        private AuthData $authData;

        /**
         * AuthManager constructor.
         *
         * @param string $clientId
         * @param string $clientSecret
         */
        public function __construct(string $clientId, string $clientSecret)
        {
            $this->client = new Client(['base_uri' => self::BASE_URI]);
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->authData = new AuthData();
        }

        /**
         * Load configuration from the config file.
         *
         * @return array
         */
        private function loadConfig(): array
        {
            if (file_exists(self::CONFIG_FILE)) {
                return json_decode(file_get_contents(self::CONFIG_FILE), true) ?? [];
            }

            return [];
        }

        /**
         * Save configuration to the config file.
         *
         * @param array $configData
         */
        private function saveConfig(array $configData): void
        {
            file_put_contents(self::CONFIG_FILE, json_encode($configData));
        }


        /**
         * Check if the access token in the configuration is valid.
         *
         * @param array $configData
         * @return bool
         */
        private function hasValidAccessToken(array $configData): bool
        {
            return isset($configData['access_token']) &&
                isset($configData['expires_in']) &&
                isset($configData['refresh_token']);
        }

        /**
         * Wait for user authorization by displaying device and user code.
         *
         * @param object $deviceCodeResponse
         */
        private function waitForUserAuthorization(object $deviceCodeResponse): void
        {
            $verificationUri = $deviceCodeResponse->verification_uri;
            echo "Please visit the URL and enter the device code: {$verificationUri}\n";
            echo "User Code: {$deviceCodeResponse->user_code}\n";
            echo "Authorize the application in your browser and then press Enter in the console to continue.\n";
            readline();
        }

        /**
         * Get AuthData properties as an array.
         *
         * @return array
         */
        private function getAuthDataAsArray(): array
        {
            return [
                'access_token' => $this->authData->getAccessToken(),
                'token_type' => $this->authData->getTokenType(),
                'expires_in' => $this->authData->getExpiresIn(),
                'scope' => $this->authData->getScope(),
                'refresh_token' => $this->authData->getRefreshToken(),
            ];
        }

        /**
         * Set AuthData properties after successful authorization.
         *
         * @param object $accessToken
         */
        private function setAuthDataAfterAuthorization(object $accessToken): void
        {
            $this->authData->setAccessToken($accessToken->access_token);
            $this->authData->setTokenType($accessToken->token_type);
            $this->authData->setExpiresIn(time() + $accessToken->expires_in);
            $this->authData->setScope($accessToken->scope);
            $this->authData->setRefreshToken($accessToken->refresh_token);
        }

        /**
         * Set AuthData properties based on the configuration.
         *
         * @param array $configData
         */
        private function setAuthDataFromConfig(array $configData): void
        {
            $this->authData->setAccessToken($configData['access_token']);
            $this->authData->setTokenType($configData['token_type']);
            $this->authData->setExpiresIn($configData['expires_in']);
            $this->authData->setScope($configData['scope']);
            $this->authData->setRefreshToken($configData['refresh_token'] ?? '');
        }

        /**
         * Start the device flow and retrieve device code response.
         *
         * @return object
         */
        private function startDeviceFlow(): object
        {
            $base64Credentials = base64_encode($this->clientId . ':' . $this->clientSecret);

            try {
                $response = $this->client->request(
                    'POST',
                    self::DEVICE_CODE_URL,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' . $base64Credentials,
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                        'form_params' => [
                            'client_id' => $this->clientId,
                        ],
                    ]
                );

                $deviceCodeResponse = json_decode($response->getBody()->getContents());

                if (isset($deviceCodeResponse->device_code)) {
                    return $deviceCodeResponse;
                } else {
                    exit;
                }
            } catch (GuzzleException $exception) {
                exit;
            }
        }

        /**
         * Exchange device code for access token.
         *
         * @param string $deviceCode
         * @return object
         */
        private function exchangeDeviceCodeForToken(string $deviceCode): object
        {
            $base64Credentials = base64_encode("{$this->clientId}:{$this->clientSecret}");

            $url = self::TOKEN_URL . '?grant_type=urn:ietf:params:oauth:grant-type:device_code&device_code=' . $deviceCode;

            $headers = [
                'Authorization' => 'Basic ' . $base64Credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $content = [];

            try {
                $response = $this->client->request(
                    'POST',
                    $url,
                    [
                        'form_params' => $content,
                        'headers' => $headers,
                    ]
                );

                $responseData = $response->getBody()->getContents();
                $decodedResponse = json_decode($responseData);

                if (isset($decodedResponse->error)) {
                    exit;
                }

                return $decodedResponse;
            } catch (GuzzleException $exception) {
                exit;
            }
        }

        /**
         * Start the device flow and retrieve device code response.
         *
         * @return object
         */

        public function refreshAccessToken(): AuthData
        {
            $configData = $this->loadConfig();
        
            if (isset($configData['refresh_token'])) {
                $base64Credentials = base64_encode("{$this->clientId}:{$this->clientSecret}");
        
                $url = self::TOKEN_URL . '?grant_type=refresh_token&refresh_token=' . $configData['refresh_token'];
        
                $headers = [
                    'Authorization' => 'Basic ' . $base64Credentials,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ];
        
                $content = [];
        
                try {
                    $response = $this->client->request(
                        'POST',
                        $url,
                        [
                            'form_params' => $content,
                            'headers' => $headers,
                        ]
                    );
        
                    $accessTokenResponse = json_decode($response->getBody()->getContents());
        
                    if (isset($accessTokenResponse->error)) {
                        exit;
                    }
        
                    $this->setAuthDataAfterAuthorization($accessTokenResponse);
                    $this->saveConfig($this->getAuthDataAsArray());
        
                    return $this->authData;
                } catch (GuzzleException $exception) {
                    exit;
                }
            }
        
            return $this->authData;
        }

        /**
         * Authorize the application and retrieve or refresh access token.
         *
         * @return AuthData
         */
        public function authorize(): AuthData
        {
            $configData = $this->loadConfig(); 
            if ($this->hasValidAccessToken($configData)) {
                if($this->authData->getExpiresIn() < time()) {
                    $this->refreshAccessToken();
                    $configData = $this->loadConfig();
                } 
                $this->setAuthDataFromConfig($configData);
            } else {
                $deviceCodeResponse = $this->startDeviceFlow();
                $this->waitForUserAuthorization($deviceCodeResponse);
                $accessToken = $this->exchangeDeviceCodeForToken($deviceCodeResponse->device_code);
                $this->setAuthDataAfterAuthorization($accessToken);
                $this->saveConfig($this->getAuthDataAsArray());
            }

            return $this->authData;
        }
    }
?>
