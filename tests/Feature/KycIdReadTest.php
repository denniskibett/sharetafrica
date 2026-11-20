<?php

namespace Tests\Feature;

use App\Integrations\Contracts\IdReader;
use App\Integrations\IdReader\TesseractIdReader;
use App\Models\User;
use App\Support\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** The ID decides: what the card says fills the details regardless of what was typed (old values kept), and kyc:reread backfills. */
class KycIdReadTest extends TestCase
{
    use RefreshDatabase;

    public array $reads = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $test = $this;
        $this->app->instance(IdReader::class, new class($test) implements IdReader
        {
            public function __construct(private $test) {}

            public function read(string $path, string $side): array
            {
                return $this->test->reads[$side] ?? [];
            }
        });
    }

    public function test_the_card_overwrites_typed_details_and_keeps_the_old_ones(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254711222333']);
        Kyc::save($user, ['id_number' => '99999999', 'county' => 'Mombasa', 'town' => 'Nyali', 'occupation' => 'Teacher']);

        $this->reads = ['id_front' => ['id_number' => '12345678', 'full_name' => 'Jane Wanjiru Doe', 'district_of_birth' => 'Nairobi', 'nationality' => 'Kenyan'],
            'id_back' => ['district' => 'Kiambu', 'location' => 'Karai', 'sub_location' => 'Gikambura']];
        $this->actingAs($user)->postJson('/profile/documents/id_front', ['file' => UploadedFile::fake()->image('f.jpg', 1600, 1000)])->assertOk()->assertJsonPath('read.district_of_birth', 'Nairobi');
        $this->postJson('/profile/documents/id_back', ['file' => UploadedFile::fake()->image('b.jpg', 1600, 1000)])->assertOk();

        $kyc = Kyc::data($user->fresh());
        $this->assertSame(['12345678', 'Jane Wanjiru Doe', 'Kenyan', 'Kiambu', 'Gikambura', 'Teacher'], [$kyc['id_number'], $kyc['full_name'], $kyc['nationality'], $kyc['county'], $kyc['town'], $kyc['occupation']]);
        $this->assertSame([['id_number', '99999999'], ['county', 'Mombasa'], ['county', 'Nairobi'], ['town', 'Nyali']], collect($kyc['replaced'])->map(fn ($r) => [$r['field'], $r['old']])->all());
    }

    public function test_a_district_that_is_not_a_county_is_not_used_as_the_county(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active']);
        $this->reads = ['id_back' => ['district' => 'Kasarani', 'division' => 'Roysambu']];
        $this->actingAs($user)->postJson('/profile/documents/id_back', ['file' => UploadedFile::fake()->image('b.jpg', 1600, 1000)])->assertOk();
        $kyc = Kyc::data($user->fresh());
        $this->assertArrayNotHasKey('county', $kyc);
        $this->assertSame('Roysambu', $kyc['town']);
    }

    public function test_backfill_rereads_photos_already_uploaded(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active']);
        $this->actingAs($user)->postJson('/profile/documents/id_front', ['file' => UploadedFile::fake()->image('f.jpg', 1600, 1000)])->assertOk();
        $this->assertArrayNotHasKey('id_number', Kyc::data($user->fresh()));   // nothing read at upload (reader was off)

        $this->reads = ['id_front' => ['id_number' => '12345678', 'district_of_birth' => 'Nakuru']];
        $this->artisan('kyc:reread', ['--dry' => true])->expectsOutputToContain('county: — → Nakuru')->assertSuccessful();
        $this->assertArrayNotHasKey('id_number', Kyc::data($user->fresh()));

        $this->artisan('kyc:reread')->expectsOutputToContain('1 updated')->assertSuccessful();
        $this->assertSame(['12345678', 'Nakuru'], [Kyc::data($user->fresh())['id_number'], Kyc::data($user->fresh())['county']]);
    }

    /** Real OCR on a rendered card, when tesseract and ImageMagick are on this machine. */
    public function test_tesseract_reads_a_rendered_card(): void
    {
        $tess = trim((string) shell_exec('command -v tesseract'));
        $magick = trim((string) shell_exec('command -v magick || command -v convert'));
        $font = collect(['/System/Library/Fonts/Supplemental/Arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'])->first(fn ($f) => is_file($f));
        if (! $tess || ! $magick || ! $font) {
            $this->markTestSkipped('tesseract, ImageMagick or a font is missing');
        }
        $file = sys_get_temp_dir().'/sharet-id-'.uniqid().'.png';
        $text = "REPUBLIC OF KENYA\nID NUMBER: 12345678\nFULL NAMES\nJANE WANJIRU DOE\nDATE OF BIRTH\n05.03.1990\nSEX FEMALE\nDISTRICT OF BIRTH\nKIAMBU";
        exec(escapeshellarg($magick).' -size 1600x1000 xc:white -font '.escapeshellarg($font).' -fill black -pointsize 48 -annotate +60+90 '.escapeshellarg($text).' '.escapeshellarg($file));
        config(['services.tesseract.path' => $tess]);
        $read = app(TesseractIdReader::class)->read($file, 'id_front');
        @unlink($file);
        $this->assertSame(['12345678', '1990-03-05', 'Jane Wanjiru Doe', 'Kiambu'], [$read['id_number'] ?? null, $read['date_of_birth'] ?? null, $read['full_name'] ?? null, $read['district_of_birth'] ?? null]);
    }

    public function test_verification_shows_one_guided_selfie(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active']);
        $this->actingAs($user)->get('/profile/edit')->assertOk()
            ->assertSee('kycSelfie((t, u) => uploadDoc(t, u))', false)->assertSee('straight, then left, then right')->assertDontSee('kycCamera()', false);
    }

    private function sendAll(User $user): void
    {
        foreach (['id_front', 'id_back', 'selfie_center', 'selfie_left', 'selfie_right'] as $t) {
            $this->actingAs($user)->postJson("/profile/documents/$t", ['file' => UploadedFile::fake()->image("$t.jpg", 1600, 1000)])->assertOk();
        }
    }

    public function test_verified_only_when_the_checks_pass(): void
    {
        $this->reads = ['id_front' => ['id_number' => '12345678', 'full_name' => 'Dennis Kibet Koech', 'date_of_birth' => '1994-03-24']];
        $ok = User::factory()->create(['name' => 'Dennis Kibett', 'phone' => '+254711000001', 'onboarding_status' => 'active']);
        $this->sendAll($ok);
        $this->assertSame(['basic', 'approved'], [$ok->fresh()->kyc_level, $ok->fresh()->kyc_review]);   // one letter of spelling allowed

        // Same ID on a second account: waits for staff
        $twin = User::factory()->create(['name' => 'Dennis Koech', 'phone' => '+254711000002', 'onboarding_status' => 'active']);
        $this->sendAll($twin);
        $this->assertSame(['none', 'pending'], [$twin->fresh()->kyc_level, $twin->fresh()->kyc_review]);
        $checks = collect(Kyc::data($twin->fresh())['review']['checks'])->mapWithKeys(fn ($c) => [$c[0] => $c[2]]);
        $this->assertFalse($checks['unique']);
        $this->assertTrue($checks['name']);
        $this->actingAs($twin)->getJson('/profile/data')->assertJsonPath('level.basic.review.key', 'pending');
    }

    public function test_name_mismatch_minor_and_unreadable_ids_wait_for_staff(): void
    {
        $this->assertFalse(Kyc::namesMatch('Juma Jones', 'Jane Wanjiru Doe'));
        $this->assertTrue(Kyc::namesMatch('Jane Doe', 'JANE WANJIRU DOE'));

        $this->reads = ['id_front' => ['id_number' => '22222222', 'full_name' => 'Jane Wanjiru Doe', 'date_of_birth' => now()->subYears(16)->toDateString()]];
        $kid = User::factory()->create(['name' => 'Jane Doe', 'phone' => '+254711000003', 'onboarding_status' => 'active']);
        $this->sendAll($kid);
        $this->assertSame('pending', $kid->fresh()->kyc_review);

        $this->reads = [];
        $blank = User::factory()->create(['name' => 'Juma Jones', 'phone' => '+254711000004', 'onboarding_status' => 'active']);
        $this->sendAll($blank);
        $this->assertSame(['none', 'pending'], [$blank->fresh()->kyc_level, $blank->fresh()->kyc_review]);
    }

    public function test_staff_approve_or_reject_and_a_new_photo_starts_over(): void
    {
        $this->seed([\Database\Seeders\RolePermissionSeeder::class]);
        $staff = User::factory()->create(['name' => 'Amina Otieno']);
        $staff->assignRole('super_admin');
        $person = User::factory()->create(['name' => 'Juma Jones', 'phone' => '+254711000005', 'onboarding_status' => 'active']);
        $this->sendAll($person);   // nothing readable: waits

        $this->actingAs($staff)->get('/admin/verifications')->assertOk()->assertSee('Juma Jones');
        $this->actingAs(User::factory()->create())->get('/admin/verifications')->assertForbidden();

        $this->actingAs($staff)->postJson("/users/{$person->id}/verification", ['decision' => 'reject'])->assertJsonValidationErrors('reason');
        $this->postJson("/users/{$person->id}/verification", ['decision' => 'reject', 'reason' => 'The ID photo is blurred.'])->assertOk();
        $this->assertSame(['none', 'rejected'], [$person->fresh()->kyc_level, $person->fresh()->kyc_review]);
        $this->actingAs($person->fresh())->getJson('/profile/data')->assertJsonPath('level.basic.review.text', 'We could not verify you: The ID photo is blurred.');

        $this->actingAs($staff)->postJson("/users/{$person->id}/verification", ['decision' => 'approve'])->assertOk();
        $this->assertSame('basic', $person->fresh()->kyc_level);

        // A new ID photo means a fresh check: approval no longer stands
        $this->travel(2)->seconds();
        $this->actingAs($person->fresh())->postJson('/profile/documents/id_front', ['file' => UploadedFile::fake()->image('new.jpg', 1600, 1000)])->assertOk();
        $this->assertSame(['none', 'pending'], [$person->fresh()->kyc_level, $person->fresh()->kyc_review]);

        // Staff cannot verify themselves
        $this->sendAll($staff);
        $this->actingAs($staff)->postJson("/users/{$staff->id}/verification", ['decision' => 'approve'])->assertForbidden();
    }
}
