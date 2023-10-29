<?php

    require_once __DIR__ . '/vendor/autoload.php';

    use Dotenv\Dotenv;
    use GuzzleHttp\Exception\GuzzleException;

    use KamilDul\CategorySynchro\Allegro\AuthManager\AuthManager;

    $authorizationManager = new AuthManager($_ENV['ALLEGRO_CLIENT_ID'], $_ENV['ALLEGRO_CLIENT_SECRET']);
    try {
        $authorizationData = $authorizationManager->authorize();
    } catch (GuzzleException $exception) {
        echo 'Authorization error: ' . $exception->getMessage();
        die;
    }

	print_r($authorizationData);

?>