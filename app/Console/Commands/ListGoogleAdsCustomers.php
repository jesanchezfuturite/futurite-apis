<?php
// app/Console/Commands/ListGoogleAdsCustomers.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\ListAccessibleCustomersRequest;
use Google\Ads\GoogleAds\V16\Services\GoogleAdsServiceClient;

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
        $request = new ListAccessibleCustomersRequest();

        // Llamar al método listAccessibleCustomers con el objeto request
        $response = $customerServiceClient->listAccessibleCustomers($request);

        // Obtener el servicio GoogleAdsServiceClient para realizar consultas
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

        foreach ($response->getResourceNames() as $customerResourceName) {
            $query = "
                SELECT
                    customer.id,
                    customer.descriptive_name,
                    customer.currency_code,
                    customer.time_zone,
                    customer.manager,
                    customer.test_account,
                    customer.resource_name,
                    customer.applied_labels,
                    customer_client.client_customer,
                    customer_client.level,
                    customer_client.hidden
                FROM customer_client
                WHERE customer_client.client_customer = '$customerResourceName'
            ";

            $searchResponse = $googleAdsServiceClient->search($customerResourceName, $query);

            foreach ($searchResponse->getIterator() as $googleAdsRow) {
                $customer = $googleAdsRow->getCustomer();
                $customerClient = $googleAdsRow->getCustomerClient();

                $this->info("Customer ID: " . $customer->getId());
                $this->info("Descriptive Name: " . $customer->getDescriptiveName());
                $this->info("Client Customer: " . $customerClient->getClientCustomer());
                $this->info("Level: " . $customerClient->getLevel());
                $this->info("Manager: " . ($customer->getManager() ? 'Yes' : 'No'));
                $this->info("Currency Code: " . $customer->getCurrencyCode());
                $this->info("Time Zone: " . $customer->getTimeZone());
                $this->info("Hidden: " . ($customerClient->getHidden() ? 'Yes' : 'No'));
                $this->info("Resource Name: " . $customer->getResourceName());
                $this->info("Test Account: " . ($customer->getTestAccount() ? 'Yes' : 'No'));
                $this->info("Applied Labels: " . implode(", ", iterator_to_array($customer->getAppliedLabels())));

                $this->info("-------------------------------");
            }
        }

        return 0;
    }
}
