<?php

namespace App\Integrations\Claude;

use App\Models\User;
use App\Support\Kyc;
use Illuminate\Support\Facades\Storage;

/**
 * A second reading of a person's ID photos by Claude, for staff reviewing a verification (Administration ›
 * Verifications › a person). It reads the card, compares it with the account and the details on file, and stores
 * what it found under kyc['assist']. It is advice only: it never changes the review status, the details, or the
 * level. Staff still approve or reject (Kyc::decide). Photos go to Anthropic only when staff press the button.
 */
class DocumentReader
{
    public const FIELDS = ['full_name', 'id_number', 'date_of_birth', 'sex', 'document_type'];

    private const SYSTEM = 'You read Kenyan national ID cards (old and Maisha) and passports for a payments company\'s identity check. '
        .'Copy each field exactly as printed. Never guess or complete a partly hidden value: leave it "" when it is not on '
        .'the document or not readable. Dates as YYYY-MM-DD. Names in the order printed. In concerns, note anything that '
        .'looks edited, cut off, a photo of a screen, a different person\'s card, or text that does not agree between the two sides.';

    /**
     * Read the person's ID photos and store the result. Returns the stored assist block:
     * ['status' => read|failed, 'read' => [...], 'compare' => [[label, on_file, on_card, same]], 'concerns' => '', 'at', 'by', 'model', 'error'].
     */
    public static function assist(User $user, User $staff): array
    {
        $kyc = Kyc::data($user);
        $content = [];
        foreach (['id_front' => 'Front', 'id_back' => 'Back'] as $side => $label) {
            $path = $kyc['documents'][$side]['path'] ?? null;
            if (! $path || ! Storage::disk('local')->exists($path)) {
                continue;
            }
            $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                continue;
            }
            $content[] = ['type' => 'text', 'text' => "$label of the ID:"];
            $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $mime, 'data' => base64_encode(Storage::disk('local')->get($path))]];
        }

        $assist = ['at' => now()->toIso8601String(), 'by' => $staff->name, 'model' => Claude::model()];
        if (! $content) {
            $assist += ['status' => 'failed', 'error' => 'There is no ID photo to read.'];
        } else {
            $content[] = ['type' => 'text', 'text' => 'Read this identity document and return the fields.'];
            [$json, $error] = Claude::json(self::SYSTEM, $content, self::schema());
            $assist += $error ? ['status' => 'failed', 'error' => $error] : self::compare($user, $kyc, $json);
        }

        $kyc['assist'] = $assist;
        // Stored without Kyc::save so nothing is re-reviewed: this is advice, not a decision
        $user->forceFill(['kyc' => encrypt($kyc)])->save();
        \App\Security\Lockdown::log('staff asked Claude to read an ID', ['person' => $user->id, 'status' => $assist['status']]);

        return $assist;
    }

    private static function compare(User $user, array $kyc, array $json): array
    {
        $read = array_map(fn ($v) => is_string($v) ? trim($v) : $v, array_intersect_key($json, array_flip(self::FIELDS)));
        $norm = fn ($v) => preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $v));
        $rows = [
            ['Name on the account', $user->name, $read['full_name'] ?? '', Kyc::namesMatch((string) $user->name, (string) ($read['full_name'] ?? ''))],
            ['ID number', $kyc['id_number'] ?? '', $read['id_number'] ?? '', filled($read['id_number'] ?? null) && $norm($read['id_number']) === $norm($kyc['id_number'] ?? '')],
            ['Date of birth', $kyc['date_of_birth'] ?? '', $read['date_of_birth'] ?? '', filled($read['date_of_birth'] ?? null) && ($read['date_of_birth'] === ($kyc['date_of_birth'] ?? null))],
        ];

        return [
            'status' => 'read',
            'read' => $read,
            'legible' => (bool) ($json['legible'] ?? false),
            'compare' => array_map(fn ($r) => ['label' => $r[0], 'on_file' => (string) $r[1], 'on_card' => (string) $r[2], 'same' => (bool) $r[3]], $rows),
            'concerns' => mb_substr(trim((string) ($json['concerns'] ?? '')), 0, 500),
        ];
    }

    private static function schema(): array
    {
        return ['type' => 'object', 'additionalProperties' => false,
            'required' => array_merge(self::FIELDS, ['legible', 'concerns']),
            'properties' => array_fill_keys(self::FIELDS, ['type' => 'string']) + [
                'legible' => ['type' => 'boolean'],
                'concerns' => ['type' => 'string', 'description' => 'Anything unclear, edited-looking or inconsistent; "" when nothing'],
            ]];
    }
}
