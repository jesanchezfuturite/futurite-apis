<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Google\Ads\GoogleAds\Lib\V18\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V18\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V18\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;

use Log;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;

use App\Repositories\AdscustomersclientsRepositoryEloquent;
use App\Repositories\IndicatorsadsclientsRepositoryEloquent;
use App\Repositories\OngoingclienteserviciosRepositoryEloquent;
use App\Repositories\CampaignsRepositoryEloquent;

class UpdateGoogleAdsIndicators extends Command
{
    protected $signature = 'googleads:update-indicators';
    protected $description = 'Update Google Ads indicators for each customer and campaign';

    protected $googleAdsClient;
    protected $customersClientsRepo;
    protected $indicatorsRepo;
    protected $ongoingClientesServiciosRepo;
    protected $campaignsRepo;

    public function __construct(
        AdscustomersclientsRepositoryEloquent $customersClientsRepo,
        IndicatorsadsclientsRepositoryEloquent $indicatorsRepo,
        OngoingclienteserviciosRepositoryEloquent $ongoingClientesServiciosRepo,
        CampaignsRepositoryEloquent $campaignsRepo

    )
    {
        parent::__construct();

        try {

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

            $this->customersClientsRepo = $customersClientsRepo;
            $this->indicatorsRepo = $indicatorsRepo;
            $this->ongoingClientesServiciosRepo = $ongoingClientesServiciosRepo;
            $this->campaignsRepo = $campaignsRepo;
        } catch (\Exception $e) {
            Log::error("[Command-UpdateGoogleAdsIndicators@__construct] Exception loading configuration - " . $e->getMessage(), ['exception' => $e]);      
        }
    }

    public function handle()
    {
        // Borrar datos existentes en ads_indicators_clients
        DB::table('ads_indicators_clients')->truncate();

        // Obtener todos los registros de ads_customers_clients
        $customersClients = $this->customersClientsRepo->all();

        foreach ($customersClients as $cc) {
            // Obtener las campañas para cada cliente
            $campaigns = $this->getCampaigns($cc->customer_id);

            foreach ($campaigns as $campaign) {
                $indicators = $this->getIndicators($cc->customer_id, $campaign->campaign_id);
                $budget = $this->getBudget($cc->client_id, 7); // Obtener presupuesto de ongoing clientes_servicios

                $data = [
                    'customer_id' => $cc->customer_id,
                    'campaign_id' => $campaign->campaign_id,
                    'client_id' => $cc->client_id,
                    'impressions' => $indicators['impressions'],
                    'impressions_month' => $indicators['impressions_month'],
                    'impressions_last_month' => $indicators['impressions_last_month'],
                    'clics' => $indicators['clicks'],
                    'clics_month' => $indicators['clicks_month'],
                    'clics_last_month' => $indicators['clicks_last_month'],
                    'paid' => $indicators['paid'],
                    'paid_month' => $indicators['paid_month'],
                    'paid_last_month' => $indicators['paid_last_month'],
                    'budget' => $budget,
                ];

                $this->indicatorsRepo->create($data);
            }
        }

        Log::info('[Command-UpdateGoogleAdsIndicators] EXECUTED');
        return 0;
    }

    private function getCampaigns($customerId)
    {

        $info = $this->campaignsRepo->findWhere(
            [
                'customer_id'   => $customerId,
                'status'        => 2
            ]
        );

        return $info;


        /*
        $googleAdsClient = $this->googleAdsClient;
        $gaService = $googleAdsClient->getGoogleAdsServiceClient();

        $query = 'SELECT campaign.id, campaign.name FROM campaign';
        $response = $gaService->search(
            SearchGoogleAdsRequest::build($customerId, $query)
        );
        */

        return $response->iterateAllElements();
    }

    private function getIndicators($customerId, $campaignId)
{
    $googleAdsClient = $this->googleAdsClient;
    $gaService = $googleAdsClient->getGoogleAdsServiceClient();

    $today = Carbon::today()->toDateString();
    $firstDayOfMonth = Carbon::now()->startOfMonth()->toDateString();
    $firstDayOfLastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
    $lastDayOfLastMonth = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();

    $indicators = [
        'impressions' => 0,
        'impressions_month' => 0,
        'impressions_last_month' => 0,
        'clicks' => 0,
        'clicks_month' => 0,
        'clicks_last_month' => 0,
        'paid' => 0,
        'paid_month' => 0,
        'paid_last_month' => 0,
    ];

    $metrics = [
        'impressions' => 'metrics.impressions',
        'clicks' => 'metrics.clicks',
        'costMicros' => 'metrics.cost_micros',
    ];

    foreach ($metrics as $key => $metric) {
        $queries = [
            'today' => "SELECT $metric FROM campaign WHERE campaign.id = $campaignId AND segments.date = '$today'",
            'month' => "SELECT $metric FROM campaign WHERE campaign.id = $campaignId AND segments.date BETWEEN '$firstDayOfMonth' AND '$today'",
            'last_month' => "SELECT $metric FROM campaign WHERE campaign.id = $campaignId AND segments.date BETWEEN '$firstDayOfLastMonth' AND '$lastDayOfLastMonth'",
        ];

        foreach ($queries as $period => $query) {
            $response = $gaService->search(
                SearchGoogleAdsRequest::build($customerId, $query)
            );

            $sum = 0;
            foreach ($response->iterateAllElements() as $row) {
                $sum += $row->getMetrics()->{"get" . ucfirst(Str::camel($key))}();
            }

            $indicators[$key . ($period == 'today' ? '' : '_' . $period)] = $sum;
        }
    }

    // Conversiones se manejan igual que clicks
    $indicators['conversion'] = $indicators['clicks'];
    $indicators['conversion_month'] = $indicators['clicks_month'];
    $indicators['conversion_last_month'] = $indicators['clicks_last_month'];

    // Convertir costMicros a unidades monetarias para los campos de pago
    $indicators['paid'] = $indicators['costMicros'] / 1000000;
    $indicators['paid_month'] = $indicators['costMicros_month'] / 1000000;
    $indicators['paid_last_month'] = $indicators['costMicros_last_month'] / 1000000;

    Log::info($indicators);

    return $indicators;
}


    private function getBudget($clientId, $serviceId)
    {
        $service = $this->ongoingClientesServiciosRepo->findWhere([
            'cliente_id' => $clientId,
            'servicio_id' => $serviceId,
        ])->first();

        return $service ? $service->monto : 0;
    }
}
