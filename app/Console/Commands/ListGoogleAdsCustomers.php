<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V16\Resources\CustomerClient;
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
            $response = $customerServiceClient->listAccessibleCustomers();

            // Obtener el servicio GoogleAdsServiceClient para realizar consultas
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

            Log::info("[COMMAND-ListGoogleAdsCustomers@handle] ListGoogleAdsCustomers response " . json_encode($response));

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
            }

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
