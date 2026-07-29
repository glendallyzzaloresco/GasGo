<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteThemeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_and_update_global_theme(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->getJson('/api/theme');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'primaryColor',
                'accentColor',
                'backgroundColor',
                'sidebarBackground',
                'logoUrl',
                'footerDescription',
                'contactAddress',
                'contactPhone',
            ],
        ]);

        $response = $this->putJson('/api/theme', [
            'primaryColor' => '#123456',
            'accentColor' => '#654321',
            'backgroundColor' => '#ffffff',
            'sidebarBackground' => '#111111',
            'footerDescription' => 'Updated footer',
            'contactAddress' => 'New Address',
            'contactPhone' => '+63 999 000 0000',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.primaryColor', '#123456');
        $response->assertJsonPath('data.contactAddress', 'New Address');
    }
}
