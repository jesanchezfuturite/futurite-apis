<?php

namespace App\Console\Commands;

use App\Repositories\CustomersRepositoryEloquent;
use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V16\Services\ListAccessibleCustomersRequest;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;
use Log;

class ListGoogleAdsCustomers extends Command
{
    protected $signature = 'googleads:list-customers';
    protected $description = 'List Google Ads customers';

    protected $googleAdsClient;
    protected $customerRepo;

    public function __construct(CustomersRepositoryEloquent $customerRepo)
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

        $this->customerRepo = $customerRepo;
    }

    public function handle()
    {
        try {
            $customerServiceClient = $this->googleAdsClient->getCustomerServiceClient();

            // Crear una instancia de ListAccessibleCustomersRequest
            $listAccessibleCustomersRequest = new ListAccessibleCustomersRequest();

            // Llamar al método listAccessibleCustomers con el objeto request
            $response = $customerServiceClient->listAccessibleCustomers($listAccessibleCustomersRequest);

            // Obtener el servicio GoogleAdsServiceClient para realizar consultas
            $customerId = $this->sanitizeCustomerId(config('google-ads.login_customer_id'));
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

            // hacemos el truncate a la tabla y actualizamos todos los registros
            $this->customerRepo->truncate();

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

                try {
                    $this->customerRepo->create($info);
                } catch (\Exception $e) {
                    Log::error('Exception occurred during updating customers: ' . $e->getMessage());
                    return 1;
                }
            }

            return 0;

        } catch (ApiException $e) {
            Log::error('ApiException occurred: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            Log::error('Exception occurred CODE: ' . $e->getCode());
            Log::error('Exception occurred MESSAGE: ' . $e->getMessage());
            Log::error('Exception occurred FILE: ' . $e->getFile());
            Log::error('Exception occurred LINE: ' . $e->getLine());
            Log::error('Exception occurred TRACE: ' . json_encode($e->getTrace()));
            return 1;
        }
    }

    private function sanitizeCustomerId($customerId)
    {
        return str_replace('-', '', $customerId);
    }
}
