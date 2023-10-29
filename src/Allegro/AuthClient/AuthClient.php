<?php

    namespace KamilDul\CategorySynchro\Allegro\AuthClient;

    use GuzzleHttp\Client;
    use Psr\Http\Message\ResponseInterface;

    /**
     * AuthClient class for handling API requests with an access token.
     */
    class AuthClient
    {
        /**
         * Base URI for Allegro API.
         */
        private const BASE_URI = 'https://api.allegro.pl';

        /**
         * Guzzle HTTP client instance.
         *
         * @var Client
         */
        private Client $client;

        /**
         * Access token for authentication.
         *
         * @var string
         */
        private string $accessToken;

        /**
         * AuthClient constructor.
         *
         * @param string $accessToken Access token for authentication.
         */
        public function __construct(string $accessToken)
        {
            $this->client = new Client(['base_uri' => self::BASE_URI]);
            $this->accessToken = $accessToken;
        }

        /**
         * Perform a GET request to the Allegro API.
         *
         * @param string $route   API endpoint route.
         * @param array  $query   Query parameters.
         * @param array  $headers Additional headers.
         *
         * @return ResponseInterface
         */
        protected function get(string $route, array $query = [], array $headers = []): ResponseInterface
        {
            // Default headers for the request.
            $defaultHeaders = [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'ContentType' => 'application/vnd.allegro.public.v1+json',
            ];

            // Make the GET request using Guzzle HTTP client.
            return $this->client->request('GET', $route, [
                'headers' => array_merge($defaultHeaders, $headers),
                'query' => $query
            ]);
        }
    }

?>
