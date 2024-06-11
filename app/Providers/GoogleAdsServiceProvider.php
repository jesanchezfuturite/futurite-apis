<?php

// app/Providers/GoogleAdsServiceProvider.php

namespace App\Providers;

use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class GoogleAdsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GoogleAdsClient::class, function ($app) {
            $jsonKeyFilePath = storage_path('app/google-ads/credentials.json');

            if (!file_exists($jsonKeyFilePath)) {
                throw new \Exception('Google Ads credentials file not found.');
            }

            $credentials = json_decode(file_get_contents($jsonKeyFilePath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding JSON credentials file: ' . json_last_error_msg());
            }

            if (!isset($credentials['web'])) {
                throw new \Exception('Invalid credentials format. "web" key not found.');
            }

            $webCredentials = $credentials['web'];

            $oAuth2 = new OAuth2([
                'clientId' => $webCredentials['client_id'],
                'clientSecret' => $webCredentials['client_secret'],
                'authorizationUri' => $webCredentials['auth_uri'],
                'tokenCredentialUri' => $webCredentials['token_uri'],
                'redirectUri' => $webCredentials['redirect_uris'][0],
                'scope' => 'https://www.googleapis.com/auth/adwords',
                'access_type' => 'offline', // Asegúrate de incluir access_type=offline
                'approval_prompt' => 'force' // Forzar el prompt de aprobación para obtener el refresh token
            ]);

            $token = $this->loadAccessToken();
            if ($token) {
                $oAuth2->updateToken($token);

                // Renovar el token si es necesario
                if ($oAuth2->isAccessTokenExpired()) {
                    $oAuth2->refreshToken($token['refresh_token']);
                    $this->saveAccessToken($oAuth2->getToken());
                }
            } else {
                $authUrl = $oAuth2->buildFullAuthorizationUri();
                throw new \Exception("Please visit the following URL to authorize your application: $authUrl");
            }

            return (new GoogleAdsClientBuilder())
                ->withOAuth2Credential($oAuth2)
                ->withDeveloperToken($webCredentials['developer_token'])
                ->build();
        });
    }

    private function loadAccessToken()
    {
        // Cargar el token de acceso desde un almacenamiento seguro
        if (Storage::disk('local')->exists('google-ads-token.json')) {
            return json_decode(Storage::disk('local')->get('google-ads-token.json'), true);
        }

        return null;
    }

    private function saveAccessToken(array $token)
    {
        // Guardar el token de acceso en un almacenamiento seguro
        Storage::disk('local')->put('google-ads-token.json', json_encode($token));
    }

    public function boot()
    {
        //
    }
}
