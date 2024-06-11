<?php

// tests/Feature/GoogleAdsControllerTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class GoogleAdsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Crear el directorio donde se almacenarán los tokens, si no existe
        Storage::fake('local');
    }

    /** @test */
    public function it_handles_google_ads_callback_and_stores_tokens()
    {
        // Simula la respuesta de Google con el token de acceso y refresh token
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test_access_token',
                'expires_in' => 3600,
                'refresh_token' => 'test_refresh_token',
                'scope' => 'https://www.googleapis.com/auth/adwords',
                'token_type' => 'Bearer'
            ], 200)
        ]);

        // Simula la solicitud GET a la ruta de callback con el código de autorización
        $response = $this->get('/google-ads/callback?code=test_auth_code');

        // Verifica que el token se haya almacenado correctamente
        Storage::disk('local')->assertExists('google-ads-token.json');

        $tokenContent = Storage::disk('local')->get('google-ads-token.json');
        $tokenData = json_decode($tokenContent, true);

        $this->assertArrayHasKey('access_token', $tokenData);
        $this->assertEquals('test_access_token', $tokenData['access_token']);
        $this->assertArrayHasKey('refresh_token', $tokenData);
        $this->assertEquals('test_refresh_token', $tokenData['refresh_token']);

        // Verifica que el usuario sea redirigido con un mensaje de éxito
        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Google Ads authenticated successfully!');
    }

    /** @test */
    public function it_fails_to_store_tokens_if_no_refresh_token_is_present()
    {
        // Simula la respuesta de Google sin el refresh token
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test_access_token',
                'expires_in' => 3600,
                'scope' => 'https://www.googleapis.com/auth/adwords',
                'token_type' => 'Bearer'
            ], 200)
        ]);

        // Simula la solicitud GET a la ruta de callback con el código de autorización
        $response = $this->get('/google-ads/callback?code=test_auth_code');

        // Verifica que el token no se haya almacenado
        Storage::disk('local')->assertMissing('google-ads-token.json');

        // Verifica que el usuario sea redirigido con un mensaje de error
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Failed to obtain refresh token. Please authorize the application again.');
    }
}

