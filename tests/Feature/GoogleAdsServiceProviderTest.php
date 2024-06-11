<?php

// tests/Feature/GoogleAdsServiceProviderTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;

class GoogleAdsServiceProviderTest extends TestCase
{
    /**
     * Test if the GoogleAdsClient is resolved correctly.
     *
     * @return void
     */
    public function testGoogleAdsClientIsResolved()
    {
        // Resuelve el GoogleAdsClient desde el contenedor de servicios
        $googleAdsClient = app(GoogleAdsClient::class);

        // Verifica que la instancia es de la clase GoogleAdsClient
        $this->assertInstanceOf(GoogleAdsClient::class, $googleAdsClient);
    }
}
