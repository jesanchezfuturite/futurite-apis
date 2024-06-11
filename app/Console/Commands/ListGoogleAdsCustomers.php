<?php
/// app/Console/Commands/ListGoogleAdsCustomers.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\ListAccessibleCustomersRequest;

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

        foreach ($response->getResourceNames() as $customer) {
            $this->info("Customer: $customer");
        }

        return 0;

    }
}
