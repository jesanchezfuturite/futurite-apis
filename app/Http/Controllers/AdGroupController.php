<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

use Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V21\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;

// repositories locale
use App\Repositories\AdscustomersclientsRepositoryEloquent;
use App\Repositories\CustomersRepositoryEloquent;
use App\Repositories\CampaignsRepositoryEloquent;
use App\Repositories\IndicatorsadsclientsRepositoryEloquent;

// repositories ongoing
use App\Repositories\OngoingclientesRepositoryEloquent;
use App\Repositories\OngoingclienteserviciosRepositoryEloquent;

class AdGroupController extends Controller
{
    protected $googleAdsClient;
    protected $clientesOngoing;
    protected $clienteServiciosOngoing;

    protected $customers;
    protected $campaigns;
    protected $customersClients;
    protected $indicators;

    protected $serviceAds = [7]; // agregar un elemento por cada servicio que definen a un cliente de ADS

    public function __construct(
        OngoingclientesRepositoryEloquent $clientesOngoing,
        OngoingclienteserviciosRepositoryEloquent $clienteServiciosOngoing,
        CustomersRepositoryEloquent $customers,
        AdscustomersclientsRepositoryEloquent $customersClients,
        CampaignsRepositoryEloquent $campaigns,
        IndicatorsadsclientsRepositoryEloquent $indicators
    )
    {

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

        // instancia de los repositorios
        $this->clientesOngoing          = $clientesOngoing;
        $this->clienteServiciosOngoing  = $clienteServiciosOngoing;
        $this->customers                = $customers;
        $this->customersClients         = $customersClients;
        $this->campaigns                = $campaigns;
        $this->indicators               = $indicators;
    }

    public function getAdGroupsByCustomerId($customerId)
    {

        if (!$customerId) {
            return response()->json(['error' => 'customer_id is required'], 400);
        }

        try {
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

            // $query = "SELECT ad_group.id, ad_group.name, ad_group.status FROM ad_group ORDER BY ad_group.id";

            $query =
            'SELECT ad_group.id, '
            . 'ad_group_ad.ad.id, '
            . 'ad_group_ad.ad.responsive_search_ad.headlines, '
            . 'ad_group_ad.ad.responsive_search_ad.descriptions, '
            . 'ad_group_ad.status '
            . 'FROM ad_group_ad '
            . 'WHERE ad_group_ad.status != "REMOVED"';
        // if (!is_null($adGroupId)) {
        //     $query .= " AND ad_group.id = $adGroupId";
        // }

            $searchRequest = new SearchGoogleAdsRequest([
                'customer_id' => $customerId,
                'query' => $query,
                'page_size' => 1000
            ]);

            $response = $googleAdsServiceClient->search($searchRequest);

            $adGroups = [];
            foreach ($response->iterateAllElements() as $googleAdsRow) {
                $adGroup = $googleAdsRow->getAdGroupAd();
                $adGroups[] = $adGroup;
            }

            return response()->json(['ad_groups' => $adGroups]);

        } catch (ApiException $e) {
            Log::error('Google Ads API error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch ad groups'], 500);
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
}
