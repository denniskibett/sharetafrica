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
        $data = self::review($user, $data);
        $user->forceFill([
            'kyc' => encrypt($data),
            'kyc_id_hash' => self::idHash($data['id_number'] ?? null),
            'kyc_review' => $data['review']['status'] ?? null,
        ])->save();
        self::refreshLevel($user);
    }

    /** A keyed hash of the ID number, so a second account with the same ID can be found without decrypting anyone. */
    public static function idHash(?string $idNumber): ?string
    {
        $n = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $idNumber));

        return $n === '' ? null : hash_hmac('sha256', $n, (string) config('app.key'));
    }

    /**
     * The automatic checks behind "Verified". Each: [key, label, ok, note]. All must pass for an automatic approval;
     * otherwise a person goes to staff review (Administration › Verifications).
     */
    public static function checks(User $user, array $kyc): array
    {
        $read = ($kyc['id_read']['id_front'] ?? []) + ($kyc['id_read']['id_back'] ?? []);
        $dob = $kyc['date_of_birth'] ?? null;
        $age = $dob ? rescue(fn () => \Carbon\Carbon::parse($dob)->age, null, false) : null;
        $hash = self::idHash($kyc['id_number'] ?? null);
        $dupe = $hash ? User::where('kyc_id_hash', $hash)->where('id', '!=', $user->id)->exists() : false;
        $match = self::namesMatch((string) $user->name, (string) ($kyc['full_name'] ?? ''));

        return [
            ['id_read', 'ID photo could be read', (bool) array_intersect_key($read, array_flip(['id_number', 'full_name', 'date_of_birth'])), $read ? null : 'Nothing readable on the ID photos'],
            ['id_number', 'ID number found', filled($kyc['id_number'] ?? null), null],
            ['name', 'Name on ID matches the account', $match, filled($kyc['full_name'] ?? null) ? ($match ? null : 'Account: '.$user->name.' · ID: '.$kyc['full_name']) : 'No name read from the ID'],
            ['adult', '18 or older', $age !== null && $age >= 18, $age === null ? 'No date of birth' : ($age < 18 ? "Age $age" : null)],
            ['unique', 'ID not used by another account', $hash !== null && ! $dupe, $dupe ? 'Another account has this ID number' : null],
        ];
    }

    /**
     * Two names on the account must each be found on the ID (one letter of spelling allowed, two for long names:
     * "Kibett" matches "Kibet"). An account with one name needs that one.
     */
    public static function namesMatch(string $account, string $onId): bool
    {
        $words = fn ($v) => array_values(array_filter(explode(' ', preg_replace('/[^a-z ]/', ' ', strtolower($v))), fn ($w) => strlen($w) >= 2));
        [$a, $b] = [$words($account), $words($onId)];
        if (! $a || ! $b) {
            return false;
        }
        $found = collect($a)->filter(fn ($w) => collect($b)->contains(fn ($x) => levenshtein($w, $x) <= (strlen($w) >= 6 ? 2 : 1)))->count();

        return $found >= min(2, count($a));
    }

    /** What a review was decided on: the verification photos and the details read from the ID. A change means a fresh look. */
    private static function reviewBasis(array $kyc): string
    {
        return sha1(json_encode([
            collect(self::VERIFY)->map(fn ($t) => $kyc['documents'][$t]['path'] ?? null)->all(),
            $kyc['id_number'] ?? null, $kyc['full_name'] ?? null, $kyc['date_of_birth'] ?? null,
        ]));
    }

    /**
     * Keeps $kyc['review'] current. Nothing to review until every verification photo is in. Then the checks run: all
     * passing approves automatically; otherwise it waits for staff. A staff decision stands until the photos or the
     * ID details change.
     */
    private static function review(User $user, array $kyc): array
    {
        $photos = collect(array_intersect(self::VERIFY, self::requiredDocuments($kyc)))->every(fn ($t) => filled($kyc['documents'][$t]['path'] ?? null));
        if (! $photos) {
            unset($kyc['review']);

            return $kyc;
        }
        $basis = self::reviewBasis($kyc);
        $current = $kyc['review'] ?? null;
        if ($current && ($current['basis'] ?? null) === $basis && in_array($current['by'] ?? null, ['staff', 'before_checks'], true)) {
            return $kyc;   // staff decided on exactly this
        }
        $checks = self::checks($user, $kyc);
        $pass = collect($checks)->every(fn ($c) => $c[2]);
        $kyc['review'] = [
            'status' => $pass ? 'approved' : 'pending', 'by' => 'checks', 'basis' => $basis, 'at' => now()->toIso8601String(),
            'checks' => $checks, 'reason' => null,
        ];

        return $kyc;
    }

    /** Staff decision. Rejecting asks the person to retake what the reason says; their next upload starts a fresh check. */
    public static function decide(User $user, User $staff, string $decision, ?string $reason): void
    {
        $kyc = self::data($user);
        $kyc['review'] = [
            'status' => $decision === 'approve' ? 'approved' : 'rejected', 'by' => 'staff', 'staff_id' => $staff->id, 'staff_name' => $staff->name,
            'basis' => self::reviewBasis($kyc), 'at' => now()->toIso8601String(), 'reason' => $reason,
            'checks' => self::checks($user, $kyc),
        ];
        $kyc['review_history'][] = array_diff_key($kyc['review'], ['checks' => 1, 'basis' => 1]);
        $user->forceFill(['kyc' => encrypt($kyc), 'kyc_review' => $kyc['review']['status']])->save();
        self::refreshLevel($user);
    }

    /** For the person: where their verification stands, in words. */
    public static function reviewState(array $kyc): array
    {
        $r = $kyc['review'] ?? null;

        return match ($r['status'] ?? null) {
            'approved' => ['key' => 'approved', 'text' => 'Verified'],
            'pending' => ['key' => 'pending', 'text' => 'We are checking your ID. This usually takes less than a day.'],
            'rejected' => ['key' => 'rejected', 'text' => 'We could not verify you: '.($r['reason'] ?: 'please retake your ID photos and selfie.')],
            default => ['key' => 'none', 'text' => null],
        };
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
        $submitted = filled($user->name) && filled($user->phone) && $photos === count($verify);
        $basic = $submitted && ($kyc['review']['status'] ?? null) === 'approved';   // checks passed or staff approved
        $fields = collect(self::FIELDS)->filter(fn ($k) => filled($kyc[$k] ?? null))->count();
        $required = self::requiredDocuments($kyc);
        $docs = collect($required)->filter(fn ($t) => filled($kyc['documents'][$t]['path'] ?? null))->count();

        return [
            'basic' => ['done' => (int) filled($user->name) + (int) filled($user->phone) + $photos, 'of' => 2 + count($verify), 'complete' => $basic,
                'photos' => [$photos, count($verify)], 'phone' => filled($user->phone), 'submitted' => $submitted, 'review' => self::reviewState($kyc)],
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
