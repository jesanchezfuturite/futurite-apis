<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\ListAccessibleCustomersRequest;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;
use Log;

class ListGoogleAdsCustomers extends Command
{
    protected $signature = 'googleads:list-customers';
    protected $description = 'List Google Ads customers';

    protected $googleAdsClient;

    public function __construct(GoogleAdsClient $googleAdsClient)
    {
        parent::__construct();
        $this->googleAdsClient = $googleAdsClient;
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
            // $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
            $customerId = config('google-ads.login_customer_id');
            $gaService = $this->googleAdsClient->getGoogleAdsServiceClient();

            Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListAccessibleCustomers customerId " . json_encode($customerId));
            Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListAccessibleCustomers gaService " . json_encode($gaService));


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
            Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListAccessibleCustomers response " . json_encode($response));
            $accounts = [];

           // $this->customers->truncate();

            foreach ($response->iterateAllElements() as $row) {
                Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListAccessibleCustomers row " . json_encode($row));


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
                    $accounts []= $info;
                }catch(\Exception $e){
                    dd($e->getMessage());
                }

            }

            dd($accounts);

            return response()->json('fin proceso');

            /*
            Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListAccessibleCustomers response " . json_encode($response));

            foreach ($response->getResourceNames() as $customerResourceName) {
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

                // Crear una instancia de SearchGoogleAdsRequest
                $searchRequest = new SearchGoogleAdsRequest([
                    'customerId' => $this->extractCustomerId($customerResourceName),
                    'query' => $query,
                ]);

                // Llamar al método search con el objeto SearchGoogleAdsRequest
                $searchResponse = $googleAdsServiceClient->search($searchRequest);

                foreach ($searchResponse->getIterator() as $googleAdsRow) {
                    $customerClient = $googleAdsRow->getCustomerClient();

                    $this->info("Client Customer: " . $customerClient->getClientCustomer());
                    $this->info("Level: " . $customerClient->getLevel());
                    $this->info("Manager: " . ($customerClient->getManager() ? 'Yes' : 'No'));
                    $this->info("Descriptive Name: " . $customerClient->getDescriptiveName());
                    $this->info("Currency Code: " . $customerClient->getCurrencyCode());
                    $this->info("Time Zone: " . $customerClient->getTimeZone());
                    $this->info("Internal ID: " . $customerClient->getId());
                    $this->info("Hidden: " . ($customerClient->getHidden() ? 'Yes' : 'No'));
                    $this->info("Resource Name: " . $customerClient->getResourceName());
                    $this->info("Test Account: " . ($customerClient->getTestAccount() ? 'Yes' : 'No'));
                    $this->info("Applied Labels: " . implode(", ", iterator_to_array($customerClient->getAppliedLabels())));

                    $this->info("-------------------------------");
                }
            }*/

            return 0;

        } catch (ApiException $e) {
            Log::error('ApiException occurred: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            Log::error('Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }

    private function extractCustomerId($customerResourceName)
    {
        $parts = explode('/', $customerResourceName);
        return end($parts);
    }
}
