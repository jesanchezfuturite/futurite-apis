<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Ads\GoogleAds\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;
use Google\Ads\GoogleAds\Util\V10\ResourceNames;

class GoogleAdsController extends Controller
{
    protected $googleAdsClient;

    public function __construct()
    {
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile(config_path('google-ads.yaml'))
            ->withOAuth2Credential($this->getOAuth2Credentials())
            ->build();
    }

    private function getOAuth2Credentials()
    {
        return (new OAuth2([
            'clientId' => config('google-ads.client_id'),
            'clientSecret' => config('google-ads.client_secret'),
            'authorizationUri' => 'https://accounts.google.com/o/oauth2/auth',
            'redirectUri' => route('google.ads.callback'),
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://www.googleapis.com/auth/adwords',
        ]));
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
            // Store the refresh token securely
            $refreshToken = $authToken['refresh_token'];
            // Save the refresh token in your configuration or environment
            // For example, you can manually add it to your .env file or use a secure storage solution
            // config(['google-ads.refresh_token' => $refreshToken]);
            return redirect()->route('google.ads.campaigns');
        }
    }

    public function getCampaigns()
    {
        $customerId = config('google-ads.login_customer_id');
        $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

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
        $campaigns = [];

        foreach ($response->iterateAllElements() as $row) {
            $campaigns[] = [
                'id' => $row->getCampaign()->getId(),
                'name' => $row->getCampaign()->getName(),
                'status' => $row->getCampaign()->getStatus(),
                'clicks' => $row->getMetrics()->getClicks(),
                'impressions' => $row->getMetrics()->getImpressions(),
                'ctr' => $row->getMetrics()->getCtr(),
                'average_cpc' => $row->getMetrics()->getAverageCpc(),
                'cost_micros' => $row->getMetrics()->getCostMicros(),
            ];
        }

        return response()->json($campaigns);
    }
}
