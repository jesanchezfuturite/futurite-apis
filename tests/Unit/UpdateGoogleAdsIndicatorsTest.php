<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Console\Commands\UpdateGoogleAdsIndicators;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\V16\Services\GoogleAdsServiceClient;
use Google\Ads\GoogleAds\V16\Services\SearchGoogleAdsResponse;
use Google\Ads\GoogleAds\V16\Resources\GoogleAdsRow;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateGoogleAdsIndicatorsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Google Ads Client
        $this->mockGoogleAdsClient = Mockery::mock(GoogleAdsClient::class);
        $this->mockGoogleAdsServiceClient = Mockery::mock(GoogleAdsServiceClient::class);

        // Bind the mock to the container
        $this->app->instance(GoogleAdsClient::class, $this->mockGoogleAdsClient);
    }

    public function testHandleMethodWithCostZero()
    {
        // Mock the response from Google Ads API
        $mockResponse = Mockery::mock(SearchGoogleAdsResponse::class);
        $mockResponse->shouldReceive('iterateAllElements')->andReturn($this->getMockedMetrics());

        $this->mockGoogleAdsServiceClient->shouldReceive('search')
            ->andReturn($mockResponse);

        $this->mockGoogleAdsClient->shouldReceive('getGoogleAdsServiceClient')
            ->andReturn($this->mockGoogleAdsServiceClient);

        // Run the artisan command
        Artisan::call('googleads:update-indicators');

        // Verify the database state
        $indicators = DB::table('ads_indicators_clients')->get();
        dump($indicators); // Agregar dump para depuración
        $this->assertNotEmpty($indicators, 'The indicators table should not be empty.');
        foreach ($indicators as $indicator) {
            $this->assertEquals(0, $indicator->paid, 'The cost should be zero.');
        }
    }

    private function getMockedMetrics()
    {
        $mockMetrics = [];

        $googleAdsRow = Mockery::mock(GoogleAdsRow::class);
        $googleAdsRow->shouldReceive('getMetrics->getCostMicros')->andReturn(0);
        $googleAdsRow->shouldReceive('getMetrics->getImpressions')->andReturn(100);
        $googleAdsRow->shouldReceive('getMetrics->getClicks')->andReturn(10);

        $mockMetrics[] = $googleAdsRow;

        return $mockMetrics;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
