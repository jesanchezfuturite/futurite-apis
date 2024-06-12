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

        try {
            $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

            Log::info("[COMMAND-ListGoogleAdsCampaigns@handle] CustomerId " . $customerId);

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

            $response = $gaService->search(
                SearchGoogleAdsRequest::build($customerId, $query)
            );

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

        } catch (ApiException $e) {
            Log::error('ApiException occurred: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            Log::error('Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }

    private function sanitizeCustomerId($customerId)
    {
        return str_replace('-', '', $customerId);
    }
}
