<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Smoke test: every parameter-free GET page, as a guest and as each role, answers without a server error. */
class AuditCrawlTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_answers_for_every_role(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $routes = collect(Route::getRoutes())->filter(fn ($r) => in_array('GET', $r->methods()) && ! str_contains($r->uri(), '{')
            && ! str_starts_with($r->uri(), '_') && ! in_array($r->uri(), ['logout', 'up', 'storage/{path}']) && ! str_starts_with($r->uri(), 'api/'))
            ->map(fn ($r) => '/'.ltrim($r->uri(), '/'))->unique()->values();

        $out = [];
        $users = ['guest' => null];
        foreach (['super_admin', 'admin', 'support', 'operations'] as $role) {
            $u = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual', 'phone' => '+2547'.rand(10000000, 99999999)]);
            $u->assignRole(Role::findOrCreate($role, 'web'));
            $users[$role] = $u;
        }
        foreach (['individual', 'merchant', 'business', 'techie'] as $lane) {
            $users[$lane] = User::factory()->create(['onboarding_status' => 'active', 'intent' => $lane, 'phone' => '+2547'.rand(10000000, 99999999)]);
        }
        $users['waiting'] = User::factory()->create(['onboarding_status' => 'waiting_list']);

        foreach ($users as $who => $user) {
            foreach ($routes as $uri) {
                if ($user) { $this->actingAs($user); } else { auth()->logout(); $this->app['auth']->forgetGuards(); }
                try {
                    $res = $this->get($uri);
                    $code = $res->getStatusCode();
                    if ($code >= 500) {
                        $msg = $res->exception ? class_basename($res->exception).': '.mb_substr($res->exception->getMessage(), 0, 160) : '';
                        $out[] = "$who $code $uri $msg";
                    } elseif (! in_array($code, [200, 302, 301, 403, 404, 405])) {
                        $out[] = "$who $code $uri";
                    }
                } catch (\Throwable $e) {
                    $out[] = "$who EXC $uri ".class_basename($e).': '.mb_substr($e->getMessage(), 0, 160);
                }
            }
        }
        $this->assertGreaterThan(30, $routes->count());
        $this->assertSame([], $out, "Pages with server errors:\n".implode("\n", $out));
    }
}
