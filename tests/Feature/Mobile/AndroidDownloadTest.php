<?php

namespace Tests\Feature\Mobile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AndroidDownloadTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->dir = storage_path('framework/testing/android-'.uniqid());
        File::ensureDirectoryExists($this->dir);
        config(['mobile.android_dir' => $this->dir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function publish(): void
    {
        foreach (['arm64', 'armv7'] as $abi) {
            File::put("$this->dir/sharet-1.2.0-$abi.apk", "apk-$abi");
        }
        File::put("$this->dir/android.json", json_encode(['version' => '1.2.0', 'build' => 3, 'files' => [
            'arm64' => ['file' => 'sharet-1.2.0-arm64.apk', 'size' => 19_000_000],
            'armv7' => ['file' => 'sharet-1.2.0-armv7.apk', 'size' => 16_000_000],
        ]]));
    }

    public function test_nothing_is_offered_until_an_apk_is_uploaded(): void
    {
        $this->get('/app/android')->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('/app/android');
        $this->get('/developers')->assertOk()->assertDontSee('/app/android')->assertDontSee('Android test build');
        $this->getJson('/api/mobile/v1/config')->assertJsonPath('android', null);
    }

    public function test_the_latest_apk_downloads_and_is_linked_from_the_site_and_the_app_config(): void
    {
        $this->publish();

        $r = $this->get('/app/android')->assertOk();
        $this->assertSame('application/vnd.android.package-archive', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('Sharet-1.2.0.apk', $r->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Sharet-1.2.0-32bit.apk', $this->get('/app/android/armv7')->assertOk()->headers->get('Content-Disposition'));

        // Testing only (owner, 7 Nov): offered on the Developers page, never on the home page
        $this->get('/')->assertDontSee('/app/android');
        $this->get('/developers')->assertOk()->assertSee('FOR TESTING ONLY')->assertSee('Android test build')->assertSee(route('app.android'))->assertSee(route('app.android', 'armv7'));
        $this->getJson('/api/mobile/v1/config')->assertJsonPath('android.version', '1.2.0')->assertJsonPath('android.build', 3);
    }

    public function test_only_known_phone_types_and_files_from_android_json_are_served(): void
    {
        $this->publish();
        File::put("$this->dir/android.json", json_encode(['version' => '1.2.0', 'files' => ['arm64' => ['file' => '../../.env']]]));

        $this->get('/app/android')->assertNotFound();
        $this->get('/app/android/x86')->assertNotFound();
    }
}
