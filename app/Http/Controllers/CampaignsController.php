<?php
// app/Console/Commands/ListGoogleAdsCustomers.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsRequest;

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
        $customerServiceClient = $this->googleAdsClient->getCustomerServiceClient();

        // Crear una instancia de ListAccessibleCustomersRequest
        $request = new \Google\Ads\GoogleAds\V16\Services\ListAccessibleCustomersRequest();

        // Llamar al método listAccessibleCustomers con el objeto request
        $response = $customerServiceClient->listAccessibleCustomers($request);

        // Obtener el servicio GoogleAdsServiceClient para realizar consultas
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

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

            $searchRequest = new SearchGoogleAdsRequest([
                'customer_id' => $customerResourceName,
                'query' => $query,
            ]);

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
    }
}
