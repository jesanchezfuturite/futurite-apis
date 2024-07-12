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
use App\Repositories\AdscustomersclientsRepositoryEloquent;
use App\Repositories\CustomersRepositoryEloquent;
use App\Repositories\CampaignsRepositoryEloquent;
use App\Repositories\IndicatorsadsclientsRepositoryEloquent;

// repositories ongoing
use App\Repositories\OngoingclientesRepositoryEloquent;
use App\Repositories\OngoingclienteserviciosRepositoryEloquent;

class AdsController extends Controller
{
    //

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

    /**
     *
     * ads.config - list all clients ongoing and check the relationships with google ads
     * 1 list all active clients
     * 2 check if have services like 6 (administracion), 7 (inversion)
     * 3 get customer info
     * @param any
     *
     * @return view listing clients onGoing
     *
     * */

     public function listClients()
    {
        $clientes = $this->clientesOngoing
            ->where('estatus', 1)
            ->with('services')
            ->orderBy('nombre', 'asc')->get();

        $info = array();

        foreach ($clientes as $c) {
            if ($c->services->count() > 0) {
                $serviciosOngoing = $c->services;

                foreach ($serviciosOngoing as $sO) {
                    if (in_array($sO->servicio_id, $this->serviceAds)) {
                        // Obtener el número de customers y campañas
                        $customerCount = $this->customers->customersByClientId($c->id)->count();


                        // has the ads service
                        $info []= array(
                            "id"            => $c->id,
                            "name"          => $c->nombre,
                            "ammount"       => $sO->monto,
                            "starting"      => $sO->fecha_arranque,
                            "customers"     => $customerCount,
                        );
                    }
                }
            }
        }

        $data = [
            "clientes" => $info
        ];

        return view('clients.config', $data);
    }

    /**
     *
     * listCustomersJson . returns all customers on ads accounts
     *
     * @param
     *
     * @return json
     *
     */

      public function listCustomersJson(Request $request)
      {

        $status = 2; // this is the default value to active accounts on ads

        try{
            $info = $this->customers->customersNotInAds();

            $associated = $this->customers->customersByClientId($request->id);

            $response = [
                "status"                => 200,
                "info"                  => $info,
                "associated"            => $associated,
                "associated_count"      => $associated->count(),
                "count"                 => $info->count()
            ];

        }catch( \Exception $e ){
            Log::info('[AdsController@listCustomersJson] ERROR - ' . $e->getMessage());

            $response = [
                "status" => 200,
                "info"   => $e->getMessage(),
                "count"  => 0
            ];
        }

        return response()->json($response);

      }

      /**
       * unlinks customer_id and client_id
       *
       * @param cliente_id
       * @param customer_id
       *
       * @return message to frontend
       *
       */

    public function unlinkCustomersJson(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $customerId = $request->input('customer_id');

        try{

            $delete = $this->customersClients
                ->where('client_id',$clienteId)
                ->where('customer_id',$customerId)
                ->delete();

            return response()->json(['success' => true, 'clienteId' => $clienteId,'message' => 'customer unlinked']);

        }catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }



    }

    /**
     * relate customer_id and client_id
     *
     * @param cliente_id
     * @param customer_id
     *
     * @return message to frontend
     *
     */

    public function relateCustomersJson(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $customerId = $request->input('customer_id');

        try{

            $this->customersClients->create(
                [
                    'client_id'     => $clienteId,
                    'customer_id'   => $customerId
                ]
            );

            return response()->json(['success' => true, 'clienteId' => $clienteId,'message' => 'customer linked']);

        }catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }



    }

    public function getClientStats()
    {
        // Obtener todos los registros únicos de cliente_id de la tabla indicatorsadsclients
        $indicators = $this->indicators->all()->groupBy('client_id');

        $data = [];

        foreach ($indicators as $clientId => $indicatorsGroup) {
            // Obtener el nombre del cliente desde la tabla ongoingclientes
            $client = $this->clientesOngoing->find($clientId);
            if (!$client) {
                continue;
            }

            $customerStats = [];
            $customerIds = $indicatorsGroup->pluck('customer_id')->unique();

            foreach ($customerIds as $customerId) {
                // Obtener el nombre del customer
                $customer = $this->customers->findWhere(['customer_id' => $customerId])->first();
                if (!$customer) {
                    continue;
                }

                $campaignStats = [];
                $campaignIds = $indicatorsGroup->where('customer_id', $customerId)->pluck('campaign_id')->unique();

                foreach ($campaignIds as $campaignId) {
                    // Obtener el nombre de la campaña
                    $campaign = $this->campaigns->findWhere(['campaign_id' => $campaignId])->first();
                    if (!$campaign) {
                        continue;
                    }

                    // Obtener los indicadores para la campaña actual
                    $indicators = $indicatorsGroup->where('customer_id', $customerId)->where('campaign_id', $campaignId)->first();

                    $campaignStats[] = [
                        'campaign_name' => $campaign->name,
                        'indicators' => $indicators,
                    ];
                }

                $customerStats[] = [
                    'customer_name' => $customer->descriptive_name,
                    'customer_id' => $customer->customer_id,
                    'campaigns' => $campaignStats,
                ];
            }

            $data[] = [
                'client_name' => $client->nombre,
                'customers' => $customerStats,
            ];
        }

        return response()->json($data);
    }



}
