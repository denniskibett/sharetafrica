<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The public pages generated from the approved canvas boards Web*V2 (scratchpad build_pages.py). */
class WebsitePagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'personal' => ['/personal', "They don't need Sharet.", null],
            'business' => ['/merchants', 'Get paid on every wallet.', 'merchants.signup'],
            'developers' => ['/developers', 'Build on Sharet.', 'developers.apply'],
            'company' => ['/company', 'Built in Nairobi', null],
            'contact' => ['/contact', 'Talk to a real person.', 'contact.send'],
            'trade' => ['/trade', 'Pay suppliers now.', 'trade.apply'],
            'privacy' => ['/privacy', 'Your data, plainly.', null],
            'blog' => ['/blog', 'Notes from the ledger.', null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_page_is_the_new_design_with_its_live_parts(string $url, string $headline, ?string $form): void
    {
        $res = $this->get($url)->assertOk();
        $html = $res->getContent();

        $res->assertSee($headline, false)
            ->assertSee('css/site.css', false)
            ->assertSee('class="ui web pg"', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertDontSee('/_blob/', false)
            ->assertDontSee('[FEE]')
            ->assertDontSee('[LICENSED PARTNER]');

        if ($form) {
            $this->assertStringContainsString('action="'.route($form).'"', $html);
            $this->assertStringContainsString('name="_token"', $html);
        }
        // No link points nowhere
        $this->assertDoesNotMatchRegularExpression('/<a(?![^>]*(href|tabindex))[^>]*>/', $html);
    }

    public function test_contact_form_still_sends_and_says_thank_you(): void
    {
        $this->from('/contact')->post('/contact/send', [
            'audience' => 'individual', 'name' => 'Juma Jones', 'email' => 'juma@gmail.com', 'brief' => 'Hello from the new page.',
        ])->assertRedirect('/contact');

        $this->get('/contact')->assertOk()->assertSee('your note has been received');
    }

    public function test_blog_lists_published_posts(): void
    {
        $this->get('/blog')->assertOk()->assertSee('The first posts are on their way.');
    }
}
