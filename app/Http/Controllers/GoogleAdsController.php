<?php

// app/Http/Controllers/GoogleAdsController.php

namespace App\Http\Controllers;

use Google\Auth\OAuth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Log;

class GoogleAdsController extends Controller
{
    public function handleGoogleAdsCallback(Request $request)
    {

        Log::info("[GoogleAdsController@handleGoogleAdsCallback]");
        $jsonKeyFilePath = storage_path('app/google-ads/credentials.json');
        $credentials = json_decode(file_get_contents($jsonKeyFilePath), true);

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

        Log::info("[GoogleAdsController@handleGoogleAdsCallback] REQUEST " . json_encode($request->all()));
        Log::info("[GoogleAdsController@handleGoogleAdsCallback] Code " . json_encode($request->code));
        $code = ( strlen($request->code) > 0 ) ? $request->code : "";
        if (strlen($code) > 0) {
            Log::info("[GoogleAdsController@handleGoogleAdsCallback]  Codigo verificado");

            $oAuth2->setCode($code);
            $authToken = $oAuth2->fetchAuthToken();

            // Verificar y guardar el refresh token si está presente
            if (!isset($authToken['refresh_token'])) {
                return redirect('/')->with('error', 'Failed to obtain refresh token. Please authorize the application again.');
            }

            // Guarda el token de acceso y el token de actualización
            try{
                $st = Storage::disk('local')->put('google-ads-token.json', json_encode($authToken));
                Log::info(json_encode($st));
            }catch(\Exception $e){
                Log::info("[GoogleAdsController@handleGoogleAdsCallback] ERROR - " . json_encode($e));
            }


            return redirect('/')->with('success', 'Google Ads authenticated successfully!');
        }

        return redirect('/')->with('error', 'Failed to authenticate with Google Ads.');
    }
}
