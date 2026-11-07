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
}
