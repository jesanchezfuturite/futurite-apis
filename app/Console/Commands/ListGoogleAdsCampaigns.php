<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;
use Log;


// Repositories
use App\Repositories\CustomersRepositoryEloquent;
use App\Repositories\CampaignsRepositoryEloquent;

class ListGoogleAdsCampaigns extends Command
{
    protected $signature = 'googleads:list-campaigns';
    protected $description = 'List Google Ads campaigns for a specific customer';

    protected $googleAdsClient;

    protected $customerR ;
    protected $campaignR ;

    public function __construct(
        CustomersRepositoryEloquent $customerR,
        CampaignsRepositoryEloquent $campaignR
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

        // repositories
        $this->customerR = $customerR;
        $this->campaignR = $campaignR;
    }

    public function handle()
    {
        // Get the configuration info
        $loginCustomerId = $this->sanitizeCustomerId(env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'));

        // get all customers
        Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] Process started.");

        $customers = $this->customerR->findWhere([ 'status' => 2 ]);

        foreach($customers as $c)
        {
            // get campagaigns per customer
            $campaigns = $this->getInfoPerCustomer($c->customer_id, $loginCustomerId);
        }

        Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] Process finished successfully.");

        return 0;
    }

    private function getInfoPerCustomer($customerId, $loginCustomerId)
    {
        try {
            // Construir el cliente de Google Ads con el login-customer-id
            $googleAdsClient = (new GoogleAdsClientBuilder())
                ->withDeveloperToken($this->googleAdsClient->getDeveloperToken())
                ->withOAuth2Credential($this->googleAdsClient->getOAuth2Credential())
                ->withLoginCustomerId($loginCustomerId)
                ->build();

            $gaService = $googleAdsClient->getGoogleAdsServiceClient();

            Log::info("[COMMAND-ListGoogleAdsCampaigns@getInfoPerCustomer] CustomerId " . $customerId);
            Log::info("[COMMAND-ListGoogleAdsCampaigns@getInfoPerCustomer] LoginCustomerId " . $loginCustomerId);

            $query = '
                SELECT
                    campaign.id,
                    campaign.name,
                    campaign.status,
                    campaign.serving_status,
                    campaign.advertising_channel_type,
                    campaign.advertising_channel_sub_type,
                    campaign.start_date,
                    campaign.end_date,
                    campaign.bidding_strategy_type,
                    campaign.campaign_budget,
                    campaign.labels,
                    campaign.tracking_url_template,
                    campaign.final_url_suffix,
                    campaign.frequency_caps,
                    campaign.video_brand_safety_suitability,
                    campaign.experiment_type,
                    campaign.optimization_score
                FROM
                    campaign
            ';

            $response = $gaService->search(
                SearchGoogleAdsRequest::build($customerId, $query)
            );

            $campaigns = [];

            foreach ($response->iterateAllElements() as $row) {

                $matchCampaign = [
                    'customer_id' => $customerId,
                    'campaign_id' => $row->getCampaign()->getId()
                ];
                $campaign = [
                    'name' => $row->getCampaign()->getName(),
                    'status' => $row->getCampaign()->getStatus(),
                    'serving_status' => $row->getCampaign()->getServingStatus(),
                    'advertising_channel_type' => $row->getCampaign()->getAdvertisingChannelType(),
                    'advertising_channel_sub_type' => $row->getCampaign()->getAdvertisingChannelSubType(),
                    'start_date' => $row->getCampaign()->getStartDate(),
                    'end_date' => $row->getCampaign()->getEndDate(),
                    'bidding_strategy_type' => $row->getCampaign()->getBiddingStrategyType(),
                    'campaign_budget' => $row->getCampaign()->getCampaignBudget(),
                    'labels' => json_encode($row->getCampaign()->getLabels()),
                    'tracking_url_template' => $row->getCampaign()->getTrackingUrlTemplate(),
                    'final_url_suffix' => $row->getCampaign()->getFinalUrlSuffix(),
                    'frequency_caps' => json_encode($row->getCampaign()->getFrequencyCaps()),
                    'video_brand_safety_suitability' => $row->getCampaign()->getVideoBrandSafetySuitability(),
                    'experiment_type' => $row->getCampaign()->getExperimentType(),
                    'optimization_score' => $row->getCampaign()->getOptimizationScore(),
                ];

                try {
                    // create the campaigns registers
                    $this->campaignR->updateOrCreate($matchCampaign,$campaign);

                } catch (\Exception $e) {
                    Log::error("[COMMAND-ListGoogleAdsCampaigns@getInfoPerCustomer] Exception inserting / updating Campaigns - " . $e->getMessage());
                    exit();
                }
            }

            return 1;

        } catch (ApiException $e) {
            Log::error('API Exception occurred CODE: ' . $e->getCode());
            Log::error('API Exception occurred MESSAGE: ' . $e->getMessage());
            Log::error('API Exception occurred FILE: ' . $e->getFile());
            Log::error('API Exception occurred LINE: ' . $e->getLine());
            Log::error('API Exception occurred TRACE: ' . json_encode($e->getTrace()) );
            exit();
            return [];
        } catch (\Exception $e) {
            Log::error('Exception occurred CODE: ' . $e->getCode());
            Log::error('Exception occurred MESSAGE: ' . $e->getMessage());
            Log::error('Exception occurred FILE: ' . $e->getFile());
            Log::error('Exception occurred LINE: ' . $e->getLine());
            Log::error('Exception occurred TRACE: ' . json_encode($e->getTrace()) );
            exit();
            return [];
        }
    }

    private function sanitizeCustomerId($customerId)
    {
        return str_replace('-', '', $customerId);
    }
}
