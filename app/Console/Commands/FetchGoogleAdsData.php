<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Repositories\CampaignsRepositoryEloquent;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Util\V16\ResourceNames;


class FetchGoogleAdsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:google-ads-campaigns-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Google Ads API and store it in the database';

    protected $campaigns;


    /**
     * Execute the console command.
     */
    public function handle( CampaignsRepositoryEloquent $campaigns )
    {
        $this->campaigns = $campaigns;
        //
        try{
            $client = (new GoogleAdsClientBuilder())
            ->fromFile(config_path('google-ads.yaml'))
            ->build();
        }catch(\Exception $e){
            dd(json_encode(auth()));
        }


        $customerId = 'INSERT_CUSTOMER_ID_HERE';
        $gaService = $client->getGoogleAdsServiceClient();

        $query = '
            SELECT
                campaign.id,
                campaign.name,
                campaign.status,
                metrics.clicks,
                metrics.impressions,
                metrics.ctr,
                metrics.average_cpc,
                metrics.cost_micros
            FROM
                campaign
            WHERE
                segments.date DURING LAST_30_DAYS
        ';

        $response = $gaService->search($customerId, $query);

        foreach ($response->iterateAllElements() as $row) {
            this->campaigns->updateOrCreate(
                ['campaign_id' => $row->getCampaign()->getId()],
                [
                    'name' => $row->getCampaign()->getName(),
                    'status' => $row->getCampaign()->getStatus(),
                    'clicks' => $row->getMetrics()->getClicks(),
                    'impressions' => $row->getMetrics()->getImpressions(),
                    'ctr' => $row->getMetrics()->getCtr(),
                    'average_cpc' => $row->getMetrics()->getAverageCpc(),
                    'cost_micros' => $row->getMetrics()->getCostMicros(),
                ]
            );
        }

        $this->info('Google Ads data fetched and stored successfully.');
    }
}
