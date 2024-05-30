<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;
use Google\Ads\GoogleAds\Util\V16\ResourceNames;

class CampaignsController extends Controller
{
    protected $googleAdsClient;
    protected $configPath;

    public function __construct()
    {
        $this->configPath = base_path('config/google-ads.yaml');
        if (!file_exists($this->configPath)) {
            throw new \Exception("El archivo de configuración google-ads.yaml no existe en el directorio config.");
        }

        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile($this->configPath)
            ->withOAuth2Credential($this->getOAuth2Credentials())
            ->withDeveloperToken(config('google-ads.developer_token'))
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
        }
    }

    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $oauth2 = $this->getOAuth2Credentials();
            $oauth2->setCode($request->get('code'));
            $authToken = $oauth2->fetchAuthToken();
            $refreshToken = $authToken['refresh_token'];

            // Guardar el refresh token de manera segura
            // Por ejemplo, puedes guardarlo en el archivo .env o en una base de datos segura
            // Aquí actualizamos el archivo google-ads.yaml directamente
            $config = yaml_parse_file($this->configPath);
            $config['refresh_token'] = $refreshToken;
            file_put_contents($this->configPath, yaml_emit($config));

            return redirect()->route('google.ads.campaigns');
        }

        return redirect()->route('google.ads.authenticate');
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

        $response = $gaService->search(SearchGoogleAdsRequest::build($customerId, $query));
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
