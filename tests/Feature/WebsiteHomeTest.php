<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The website home is generated from the approved canvas board WebHomeV2 (see resources/views/index.blade.php). */
class WebsiteHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_the_approved_design_with_working_links(): void
    {
        $res = $this->get('/')->assertOk();

        $res->assertSee('Every wallet. Every border. One app.')
            ->assertSee('Every wallet talks to every wallet.')
            ->assertSee('Your money, on your phone.')
            ->assertSee('Join Sharet Kenya. Tanzania next.')
            ->assertSee('Cashia (Kashia Services Limited)')
            ->assertSee('css/site.css', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('/_blob/', false)            // canvas-only image addresses never reach the site
            ->assertDontSee('[FEE]')
            ->assertDontSee('[LICENSED PARTNER]');
    }

    public function test_hero_never_shows_a_made_up_rate_before_treasury_records_today(): void
    {
        // No FX rate recorded in a fresh database
        $this->get('/')->assertOk()
            ->assertSee("Today's rate, before you pay", false)
            ->assertDontSee('1 KES = USD 0.0076</b><br><span class="sm mu">Today', false);
    }

    public function test_every_photo_and_logo_the_site_uses_exists(): void
    {
        $css = file_get_contents(public_path('css/site.css'));
        $html = $this->get('/')->getContent();
        preg_match_all('#/images/[A-Za-z0-9/_.-]+\.(?:webp|svg|png|jpg)#', $css.$html, $m);

        $this->assertNotEmpty($m[0]);
        foreach (array_unique($m[0]) as $path) {
            $this->assertFileExists(public_path(ltrim($path, '/')), $path);
        }
    }
}
