<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;
use Log;

class ListGoogleAdsCampaigns extends Command
{
    protected $signature = 'googleads:list-campaigns {customerId}';
    protected $description = 'List Google Ads campaigns for a specific customer';

    protected $googleAdsClient;

    public function __construct(GoogleAdsClient $googleAdsClient)
    {
        parent::__construct();
        $this->googleAdsClient = $googleAdsClient;
    }

    public function handle()
    {
        $customerId = $this->sanitizeCustomerId($this->argument('customerId'));
        $loginCustomerId = config('google-ads.manager_customer_id'); // Obtén el ID del cliente administrador de la configuración

        try {
            // Configura el cliente de Google Ads con el login-customer-id
            $googleAdsClient = (new GoogleAdsClientBuilder())
                ->from($this->googleAdsClient->getGoogleAdsConfig())
                ->withLoginCustomerId($loginCustomerId)
                ->build();

            $gaService = $googleAdsClient->getGoogleAdsServiceClient();

            Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] CustomerId " . $customerId);
            Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] LoginCustomerId " . $loginCustomerId);

            $query = '
                SELECT
                    campaign.id,
                    campaign.name,
                    campaign.status,
                    campaign.serving_status,
                    campaign.advertising_channel_type,
                    campaign.start_date,
                    campaign.end_date
                FROM
                    campaign
            ';

            $searchRequest = new SearchGoogleAdsRequest([
                'customerId' => $customerId,
                'query' => $query,
            ]);

            $response = $gaService->search($searchRequest);

            Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] ListGoogleAdsCampaigns response " . json_encode($response));
            $campaigns = [];

            foreach ($response->iterateAllElements() as $row) {
                Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] ListGoogleAdsCampaigns row " . json_encode($row));

                $campaign = [
                    'id' => $row->getCampaign()->getId(),
                    'name' => $row->getCampaign()->getName(),
                    'status' => $row->getCampaign()->getStatus(),
                    'serving_status' => $row->getCampaign()->getServingStatus(),
                    'advertising_channel_type' => $row->getCampaign()->getAdvertisingChannelType(),
                    'start_date' => $row->getCampaign()->getStartDate(),
                    'end_date' => $row->getCampaign()->getEndDate()
                ];

                try {
                    $campaigns[] = $campaign;
                } catch (\Exception $e) {
                    Log::error("[COMMAND-ListGoogleAdsCampaigns@handle] Exception " . $e->getMessage());
                }
            }

            // Mostrar los resultados en la consola
            foreach ($campaigns as $campaign) {
                $this->info(json_encode($campaign));
            }

            Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] Process finished successfully.");

            return 0;

        }catch (ApiException $e) {
            Log::error('API Exception occurred CODE: ' . $e->getCode());
            Log::error('API Exception occurred MESSAGE: ' . $e->getMessage());
            Log::error('API Exception occurred FILE: ' . $e->getFile());
            Log::error('API Exception occurred LINE: ' . $e->getLine());
            Log::error('API Exception occurred TRACE: ' . json_encode($e->getTrace()) );
            return 1;
        } catch (\Exception $e) {
            Log::error('Exception occurred CODE: ' . $e->getCode());
            Log::error('Exception occurred MESSAGE: ' . $e->getMessage());
            Log::error('Exception occurred FILE: ' . $e->getFile());
            Log::error('Exception occurred LINE: ' . $e->getLine());
            Log::error('Exception occurred TRACE: ' . json_encode($e->getTrace()) );
            return 1;
        }
    }

    private function sanitizeCustomerId($customerId)
    {
        return str_replace('-', '', $customerId);
    }
}
