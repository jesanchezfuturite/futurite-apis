<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;

// repositories locale
use App\Repositories\OngoingclientesRepositoryEloquent;

// repositories ongoing


class AdsController extends Controller
{
    //
    public function __construct(

    )
    {
        parent::__construct();

        // Cargar la configuración desde el archivo INI
        $config = parse_ini_file(storage_path('app/google-ads/google-ads.ini'));

        // Inicializar el cliente de la API de Google Ads con credenciales de la cuenta de servicio
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->withJsonKeyFilePath(storage_path($config['jsonKeyFilePath']))
            ->withScopes([$config['scopes']])
            ->withImpersonatedEmail($config['impersonatedEmail'])
            ->build();

        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withDeveloperToken($config['developerToken'])
            ->withOAuth2Credential($oAuth2Credential)
            ->withLoginCustomerId($config['loginCustomerId'])
            ->build();


    }

    /**
     *
     * ads.config - pointing to client list to setup relationships to ads entities
     *
     * @param any
     *
     * @return view listing clients onGoing
     *
     * */

     public function listClients()
     {

     }
}
