<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** The public API docs (/docs) stay in step with the API: every error type the code can return is documented. */
class DocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_page_and_error_anchor_load_in_the_site_look(): void
    {
        $this->get('/docs')->assertOk()->assertSee('Sharet API docs')->assertSee('css/site.css', false)
            ->assertSee('Sharet-Signature')->assertSee('Idempotency-Key')->assertSee('id="errors"', false)->assertDontSee('/_blob/', false);
        $this->get('/docs/errors')->assertOk()->assertSee('id="account_not_found"', false);
        $this->get('/developers')->assertSee('href="'.route('docs').'"', false);
        $this->get('/')->assertSee('href="'.route('docs').'"', false);
    }

    public function test_every_error_type_the_api_returns_is_documented(): void
    {
        $code = collect(File::allFiles(app_path()))->map(fn ($f) => File::get($f->getPathname()))->implode("\n");
        preg_match_all("/(?:ApiProblem::make|new ApiError)\\(\\d{3}, '([a-z_]+)'/", $code, $m);
        $types = array_unique(array_merge($m[1], ['invalid_api_key', 'idempotency_key_required', 'idempotency_key_reused', 'in_progress']));

        $docs = $this->get('/docs')->getContent();
        $missing = array_values(array_filter($types, fn ($t) => ! str_contains($docs, 'id="'.$t.'"')));
        $this->assertSame([], $missing, 'Document these error types in resources/views/docs/index.blade.php');
    }
}
