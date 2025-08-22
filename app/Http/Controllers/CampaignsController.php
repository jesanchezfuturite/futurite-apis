<?php

namespace App\Http\Controllers;

use App\Repositories\CustomersRepositoryEloquent;

use Illuminate\Http\Request;
use Google\Ads\GoogleAds\Lib\V21\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;
use Google\Ads\GoogleAds\Util\V21\ResourceNames;
use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsStreamRequest;



use Illuminate\Support\Facades\Session;

class CampaignsController extends Controller
{
    protected $googleAdsClient;
    protected $customers;

    public function __construct(
        CustomersRepositoryEloquent $customers
    )
    {
        $this->customers = $customers;
    }

    private function getOAuth2Credentials()
    {
        $oauth2 = new OAuth2([
            'clientId' => config('google-ads.client_id'),
            'clientSecret' => config('google-ads.client_secret'),
            'authorizationUri' => 'https://accounts.google.com/o/oauth2/auth',
            'redirectUri' => route('google.ads.callback'),
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://www.googleapis.com/auth/adwords',
        ]);

        // Establecer el refresh token desde la sesión si existe
        if (Session::has('google_ads_refresh_token')) {
            $oauth2->setRefreshToken(Session::get('google_ads_refresh_token'));
        }

        return $oauth2;
    }

    public function authenticate()
    {
        $oauth2 = $this->getOAuth2Credentials();

        if (!request()->has('code')) {
            $authorizationUri = $oauth2->buildFullAuthorizationUri(['access_type' => 'offline']);
            return redirect((string) $authorizationUri);
        } else {
            $oauth2->setCode(request()->get('code'));
            $authToken = $oauth2->fetchAuthToken();
            $refreshToken = $authToken['refresh_token'];

            // Guardar el refresh token en la sesión
            Session::put('google_ads_refresh_token', $refreshToken);

            return redirect()->route('google.ads.campaigns');
        }
    }

    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $oauth2 = $this->getOAuth2Credentials();
            $oauth2->setCode($request->get('code'));
            $authToken = $oauth2->fetchAuthToken();
            $refreshToken = $authToken['refresh_token'];

            // Guardar el refresh token en la sesión
            Session::put('google_ads_refresh_token', $refreshToken);

            return redirect()->route('google.ads.campaigns');
        }

        return redirect()->route('google.ads.authenticate');
    }

    public function getCampaigns()
    {
        if (!Session::has('google_ads_refresh_token')) {
            return redirect()->route('google.ads.authenticate');
        }

        $oauth2 = $this->getOAuth2Credentials();
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile(base_path('config/google-ads.yaml'))
            ->withOAuth2Credential($oauth2)
            ->withLoginCustomerId(config('google-ads.login_customer_id'))
            ->withDeveloperToken(config('google-ads.developer_token'))
            ->build();

        $customerId = 5498625835;
        $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

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
            campaign.optimization_score,
            metrics.clicks,
            metrics.impressions,
            metrics.ctr,
            metrics.average_cpc,
            metrics.cost_micros,
            metrics.average_cost,
            metrics.cost_per_all_conversions,
            metrics.cost_per_conversion,
            metrics.conversions,
            metrics.conversions_value,
            metrics.all_conversions,
            metrics.all_conversions_value,
            metrics.cross_device_conversions,
            metrics.engagement_rate,
            metrics.engagements,
            metrics.interaction_rate,
            metrics.interactions,
            metrics.value_per_all_conversions,
            metrics.value_per_conversion,
            metrics.video_views,
            metrics.video_view_rate,
            metrics.view_through_conversions
            FROM
                campaign
            WHERE
                segments.date = \''.date('Y-m-d').'\'
        ';

        $response = $gaService->search(
            SearchGoogleAdsRequest::build($customerId, $query)
        );
        $campaigns = [];

        foreach ($response->iterateAllElements() as $row) {
            $campaign = $row->getCampaign();
            $metrics = $row->getMetrics();

            $campaigns[] = [
                'id' => $campaign->getId(),
                'name' => $campaign->getName(),
                'status' => $campaign->getStatus(),
                'serving_status' => $campaign->getServingStatus(),
                'advertising_channel_type' => $campaign->getAdvertisingChannelType(),
                'advertising_channel_sub_type' => $campaign->getAdvertisingChannelSubType(),
                'start_date' => $campaign->getStartDate(),
                'end_date' => $campaign->getEndDate(),
                'bidding_strategy_type' => $campaign->getBiddingStrategyType(),
                'campaign_budget' => $campaign->getCampaignBudget(),
                'labels' => $campaign->getLabels(),
                'tracking_url_template' => $campaign->getTrackingUrlTemplate(),
                'final_url_suffix' => $campaign->getFinalUrlSuffix(),
                //'real_time_bidding_setting' => $campaign->getRealTimeBiddingSetting(),
                'frequency_caps' => $campaign->getFrequencyCaps(),
                'video_brand_safety_suitability' => $campaign->getVideoBrandSafetySuitability(),
                'experiment_type' => $campaign->getExperimentType(),
                //'selective_optimization' => $campaign->getSelectiveOptimization(),
                'optimization_score' => $campaign->getOptimizationScore(),
                //'targeting_setting' => $campaign->getTargetingSetting(),
                //'final_urls' => $campaign->getFinalUrls(),
                //'final_mobile_urls' => $campaign->getFinalMobileUrls(),
                'clicks' => $metrics->getClicks(),
                'impressions' => $metrics->getImpressions(),
                'ctr' => $metrics->getCtr(),
                'average_cpc' => $metrics->getAverageCpc(),
                'cost_micros' => $metrics->getCostMicros(),
                'average_cost' => $metrics->getAverageCost(),
                //'average_cost_per_click' => $metrics->getAverageCostPerClick(),
                //'average_cost_per_thousand_impressions' => $metrics->getAverageCostPerThousandImpressions(),
                'cost_per_all_conversions' => $metrics->getCostPerAllConversions(),
                'cost_per_conversion' => $metrics->getCostPerConversion(),
                'conversions' => $metrics->getConversions(),
                'conversions_value' => $metrics->getConversionsValue(),
                'all_conversions' => $metrics->getAllConversions(),
                'all_conversions_value' => $metrics->getAllConversionsValue(),
                'cross_device_conversions' => $metrics->getCrossDeviceConversions(),
                'engagement_rate' => $metrics->getEngagementRate(),
                'engagements' => $metrics->getEngagements(),
                'interaction_rate' => $metrics->getInteractionRate(),
                'interactions' => $metrics->getInteractions(),
                'value_per_all_conversions' => $metrics->getValuePerAllConversions(),
                'value_per_conversion' => $metrics->getValuePerConversion(),
                'video_views' => $metrics->getVideoViews(),
                'video_view_rate' => $metrics->getVideoViewRate(),
                'view_through_conversions' => $metrics->getViewThroughConversions(),
            ];
        }

        return response()->json($campaigns);
    }

    public function getAccounts()
    {
        if (!Session::has('google_ads_refresh_token')) {
            return redirect()->route('google.ads.authenticate');
        }

        $oauth2 = $this->getOAuth2Credentials();
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile(base_path('config/google-ads.yaml'))
            ->withOAuth2Credential($oauth2)
            ->withDeveloperToken(config('google-ads.developer_token'))
            ->build();

        $customerId = config('google-ads.login_customer_id');
        $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

        $query = '
            SELECT
                customer_client.client_customer,
                customer_client.level,
                customer_client.manager,
                customer_client.descriptive_name,
                customer_client.currency_code,
                customer_client.time_zone,
                customer_client.id,
                customer_client.hidden,
                customer_client.resource_name,
                customer_client.test_account,
                customer_client.applied_labels
            FROM
                customer_client
            WHERE
                customer_client.level <= 1
        ';

        $response = $gaService->search(
            SearchGoogleAdsRequest::build($customerId, $query)
        );

        $accounts = [];

        $this->customers->truncate();

        foreach ($response->iterateAllElements() as $row) {


            $info = [
                'client_customer' => $row->getCustomerClient()->getClientCustomer(),
                'level' => $row->getCustomerClient()->getLevel(),
                'manager' => $row->getCustomerClient()->getManager(),
                'descriptive_name' => $row->getCustomerClient()->getDescriptiveName(),
                'currency_code' => $row->getCustomerClient()->getCurrencyCode(),
                'time_zone' => $row->getCustomerClient()->getTimeZone(),
                'internal_id' => $row->getCustomerClient()->getId(),
                'hidden' => $row->getCustomerClient()->getHidden(),
                'resource_name' => $row->getCustomerClient()->getResourceName(),
                'test_account' => $row->getCustomerClient()->getTestAccount(),
                'applied_labels' => json_encode($row->getCustomerClient()->getAppliedLabels())
            ];

            try{
                $this->customers->create($info);
            }catch(\Exception $e){
                dd($e->getMessage());
            }

        }

        return response()->json('fin proceso');
    }


    public function getCampaignsByCustomerId()
    {
        if (!Session::has('google_ads_refresh_token')) {
            return redirect()->route('google.ads.authenticate');
        }

        $oauth2 = $this->getOAuth2Credentials();
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withOAuth2Credential($oauth2)
            ->withLoginCustomerId(config('google-ads.login_customer_id'))
            ->withDeveloperToken(config('google-ads.developer_token'))
            ->build();

        $customerId = config('google-ads.login_customer_id');
        $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

        $query = 'SELECT campaign.id, campaign.name, campaign.status, campaign.serving_status, campaign.start_date, campaign.end_date FROM campaign';

        $response = $gaService->search(
            SearchGoogleAdsRequest::build(config('google-ads.login_customer_id'), $query)
        );

        $campaigns = [];
        /** @var GoogleAdsRow $row */
        foreach ($response->iterateAllElements() as $row) {
            $campaign = $row->getCampaign();
            $campaigns[] = [
                'id' => $campaign->getId(),
                'name' => $campaign->getName(),
                'status' => $campaign->getStatus(),
                'serving_status' => $campaign->getServingStatus(),
                'start_date' => $campaign->getStartDate(),
                'end_date' => $campaign->getEndDate(),
            ];
        }

        return response()->json($campaigns);
    }
}
