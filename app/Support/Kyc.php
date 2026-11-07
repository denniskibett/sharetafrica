<?php

namespace App\Support;

use App\Integrations\Contracts\IdReader;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Personal account levels and the details behind them (users.kyc, encrypted).
 *
 *   basic     verified: name, M-Pesa number, ID photos (front and back) and the three selfies. Basic limits, enough
 *             to send and receive. The ID's details are read from the photo (IdReader), not typed.
 *   standard  bank grade: ID, KRA PIN, address, work and income, next of kin, PEP answer,
 *             plus clear photos: ID front and back (or passport page), selfies centre/left/right, signature.
 *             Needed for loans, cards, trade and higher limits, and to register a business.
 *
 * Document files are kept on the private disk (storage/app/private/kyc/{user}) and are only
 * streamed to their owner or to staff with admin.users.
 */
class Kyc
{
    /** Photos that verify an account (basic level). A passport has no back. */
    public const VERIFY = ['id_front', 'id_back', 'selfie_center', 'selfie_left', 'selfie_right'];

    public const FIELDS = ['id_type', 'id_number', 'date_of_birth', 'kra_pin', 'nationality', 'county', 'town', 'address',
        'occupation', 'income_band', 'source_of_funds', 'next_of_kin_name', 'next_of_kin_phone'];

    /** type => [label, hint, kind] kind: photo (ID image), selfie (camera), signature */
    public const DOCUMENTS = [
        'id_front' => ['ID: front', 'All four corners in frame, no glare, text readable.', 'photo'],
        'id_back' => ['ID: back', 'All four corners in frame, no glare.', 'photo'],
        'selfie_center' => ['Selfie: face the camera', 'Look straight at the camera in good light.', 'selfie'],
        'selfie_left' => ['Selfie: turn left', 'Turn your head to your left.', 'selfie'],
        'selfie_right' => ['Selfie: turn right', 'Turn your head to your right.', 'selfie'],
        'signature' => ['Signature', 'Sign as you do on official documents.', 'signature'],
    ];

    public static function data(User $user): array
    {
        try {
            return $user->kyc ? (array) decrypt($user->kyc) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public static function save(User $user, array $data): void
    {
        $user->forceFill(['kyc' => encrypt($data)])->save();
        self::refreshLevel($user);
    }

    /** Documents still needed (a passport has no back side). */
    public static function requiredDocuments(array $kyc): array
    {
        return array_keys(array_filter(self::DOCUMENTS, fn ($d, $type) => ! ($type === 'id_back' && ($kyc['id_type'] ?? null) === 'passport'), ARRAY_FILTER_USE_BOTH));
    }

    public static function progress(User $user): array
    {
        $kyc = self::data($user);
        $verify = array_values(array_intersect(self::VERIFY, self::requiredDocuments($kyc)));
        $photos = collect($verify)->filter(fn ($t) => filled($kyc['documents'][$t]['path'] ?? null))->count();
        $basic = filled($user->name) && filled($user->phone) && $photos === count($verify);
        $fields = collect(self::FIELDS)->filter(fn ($k) => filled($kyc[$k] ?? null))->count();
        $required = self::requiredDocuments($kyc);
        $docs = collect($required)->filter(fn ($t) => filled($kyc['documents'][$t]['path'] ?? null))->count();

        return [
            'basic' => ['done' => (int) filled($user->name) + (int) filled($user->phone) + $photos, 'of' => 2 + count($verify), 'complete' => $basic,
                'photos' => [$photos, count($verify)], 'phone' => filled($user->phone)],
            'standard' => [
                'done' => $fields + $docs, 'of' => count(self::FIELDS) + count($required),
                'fields' => [$fields, count(self::FIELDS)], 'documents' => [$docs, count($required)],
                'complete' => $basic && $fields === count(self::FIELDS) && $docs === count($required) && array_key_exists('pep', $kyc),
            ],
        ];
    }

    public static function refreshLevel(User $user): string
    {
        $p = self::progress($user);
        $level = $p['standard']['complete'] ? 'standard' : ($p['basic']['complete'] ? 'basic' : 'none');
        if ($user->kyc_level !== $level) {
            $user->forceFill(['kyc_level' => $level, 'kyc_submitted_at' => $level === 'standard' ? now() : $user->kyc_submitted_at])->save();
        }

        return $level;
    }

    public static function isBankGrade(User $user): bool
    {
        return self::refreshLevel($user) === 'standard';
    }

    /** Stores an uploaded photo or a data: URL (camera selfie, drawn signature). Returns the private path. */
    public static function storeDocument(User $user, string $type, UploadedFile|string $file): string
    {
        $kyc = self::data($user);
        $old = $kyc['documents'][$type]['path'] ?? null;
        $dir = "kyc/{$user->id}";

        if ($file instanceof UploadedFile) {
            $path = $file->storeAs($dir, $type.'-'.now()->format('YmdHis').'.'.$file->extension(), 'local');
        } else {
            [$meta, $b64] = explode(',', $file, 2);
            $ext = str_contains($meta, 'png') ? 'png' : 'jpg';
            $path = "$dir/$type-".now()->format('YmdHis').".$ext";
            Storage::disk('local')->put($path, base64_decode($b64));
        }

        $old && $old !== $path && Storage::disk('local')->delete($old);
        $kyc['documents'][$type] = ['path' => $path, 'uploaded_at' => now()->toIso8601String()];
        if (in_array($type, ['id_front', 'id_back'], true)) {
            $kyc = self::readId($kyc, $type, Storage::disk('local')->path($path));
        }
        self::save($user, $kyc);

        return $path;
    }

    /** Kenya's 47 counties: an old ID's district only becomes the county when it is one of these. */
    public const COUNTIES = ['Baringo', 'Bomet', 'Bungoma', 'Busia', 'Elgeyo Marakwet', 'Embu', 'Garissa', 'Homa Bay', 'Isiolo',
        'Kajiado', 'Kakamega', 'Kericho', 'Kiambu', 'Kilifi', 'Kirinyaga', 'Kisii', 'Kisumu', 'Kitui', 'Kwale', 'Laikipia', 'Lamu',
        'Machakos', 'Makueni', 'Mandera', 'Marsabit', 'Meru', 'Migori', 'Mombasa', 'Muranga', 'Nairobi', 'Nakuru', 'Nandi', 'Narok',
        'Nyamira', 'Nyandarua', 'Nyeri', 'Samburu', 'Siaya', 'Taita Taveta', 'Tana River', 'Tharaka Nithi', 'Trans Nzoia', 'Turkana',
        'Uasin Gishu', 'Vihiga', 'Wajir', 'West Pokot'];

    /** Details the ID decides: what the card says replaces what was typed (the old value is kept under 'replaced'). */
    public const FROM_ID = ['id_number', 'date_of_birth', 'full_name', 'sex', 'nationality', 'county', 'town'];

    /** Re-reads the ID photos already uploaded (backfill). Returns [field => [old, new]] for what changed. */
    public static function reread(User $user, bool $save = true): array
    {
        $kyc = self::data($user);
        $before = array_intersect_key($kyc, array_flip(self::FROM_ID));
        foreach (['id_front', 'id_back'] as $side) {
            $path = $kyc['documents'][$side]['path'] ?? null;
            if ($path && Storage::disk('local')->exists($path)) {
                $kyc = self::readId($kyc, $side, Storage::disk('local')->path($path));
            }
        }
        $changes = [];
        foreach (self::FROM_ID as $f) {
            if (($kyc[$f] ?? null) !== ($before[$f] ?? null)) {
                $changes[$f] = [$before[$f] ?? null, $kyc[$f] ?? null];
            }
        }
        $save && $changes && self::save($user, $kyc);

        return $changes;
    }

    /** What the ID photo says, kept under id_read; it fills the details regardless of what was typed, keeping the old value. */
    private static function readId(array $kyc, string $side, string $file): array
    {
        try {
            $read = app(IdReader::class)->read($file, $side);
        } catch (\Throwable $e) {
            report($e);
            $read = [];
        }
        $kyc['id_read'][$side] = $read + ['at' => now()->toIso8601String()];
        if (! $read) {
            return $kyc;
        }
        $kyc['id_type'] = ($kyc['id_type'] ?? null) === 'passport' && ! isset($read['id_number']) ? 'passport' : 'national_id';
        $read += self::places($read);
        foreach (self::FROM_ID as $field) {
            $new = $read[$field] ?? null;
            $old = $kyc[$field] ?? null;
            if (blank($new) || $new === $old) {
                continue;
            }
            if (filled($old)) {
                $kyc['replaced'][] = ['field' => $field, 'old' => $old, 'new' => $new, 'side' => $side, 'at' => now()->toIso8601String()];
            }
            $kyc[$field] = $new;
        }

        return $kyc;
    }

    /** County and town from the places printed on the card: the district (or district of birth) when it is a county; the location for the town. */
    private static function places(array $read): array
    {
        $county = collect([$read['county'] ?? null, $read['district'] ?? null, $read['district_of_birth'] ?? null])
            ->filter()->map(fn ($v) => collect(self::COUNTIES)->first(fn ($c) => strcasecmp(str_replace(["'", '-'], ['', ' '], $v), $c) === 0))->filter()->first();
        $town = $read['sub_location'] ?? $read['location'] ?? $read['division'] ?? $read['place_of_issue'] ?? null;

        return array_filter(['county' => $county, 'town' => $town]);
    }

    /** Document list for a page: label, hint, kind, uploaded?, when, and the private URL to view it. */
    public static function documents(User $user, string $route): array
    {
        $kyc = self::data($user);
        $required = self::requiredDocuments($kyc);

        return collect(self::DOCUMENTS)->only($required)->map(fn ($d, $type) => [
            'type' => $type, 'label' => $d[0], 'hint' => $d[1], 'kind' => $d[2], 'verify' => in_array($type, self::VERIFY, true),
            'uploaded' => filled($kyc['documents'][$type]['path'] ?? null),
            'at' => isset($kyc['documents'][$type]['uploaded_at']) ? \Illuminate\Support\Carbon::parse($kyc['documents'][$type]['uploaded_at'])->timezone('Africa/Nairobi')->format('j M Y, H:i') : null,
            'url' => filled($kyc['documents'][$type]['path'] ?? null) ? route($route, ['user' => $user->id, 'type' => $type, 'v' => crc32($kyc['documents'][$type]['path'])]) : null,
        ])->values()->all();
    }
}
