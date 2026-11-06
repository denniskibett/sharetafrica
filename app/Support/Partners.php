<?php

namespace App\Support;

/**
 * Logos of banks, mobile money, PesaLink and card schemes (public/images/partners, listed in partners.json with
 * where each came from). Used only to show people where their money goes.
 *   Partners::logo('equity')  → asset URL or null
 *   Partners::forBank('Equity Bank Kenya Limited') → 'equity'
 */
class Partners
{
    protected static ?array $all = null;

    public static function all(): array
    {
        if (self::$all === null) {
            $file = public_path('images/partners/partners.json');
            $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
            unset($data['_note']);
            self::$all = $data ?: [];
        }

        return self::$all;
    }

    public static function logo(?string $slug): ?string
    {
        $p = $slug ? (self::all()[$slug] ?? null) : null;

        return $p ? asset('images/partners/'.$p['file']) : null;
    }

    /** Logos drawn in white for dark backgrounds (their tile gets an ink background). */
    public static function onDark(?string $slug): bool
    {
        return (bool) ($slug ? (self::all()[$slug]['on_dark'] ?? false) : false);
    }

    /** Best-effort match from a bank's legal name (CBK list) or a short code to a logo slug. */
    public static function forBank(string $name): ?string
    {
        $n = strtolower($name);
        if (isset(self::all()[$n])) {
            return $n;
        }
        $words = ['absa' => 'absa', 'access' => 'access', 'african banking' => 'abc', 'bank of africa' => 'boa', 'citi' => 'citi',
            'consolidated' => 'consolidated', 'co-operative' => 'coop', 'cooperative' => 'coop', 'credit bank' => 'credit',
            'development bank' => 'dbk', 'diamond trust' => 'dtb', 'dib' => 'dib', 'equity' => 'equity', 'family' => 'family',
            'guardian' => 'guardian', 'gulf african' => 'gulf', 'habib' => 'habib', 'i&m' => 'imbank', 'kcb' => 'kcb',
            'middle east' => 'meb', 'national bank' => 'nbk', 'ncba' => 'ncba', 'paramount' => 'paramount', 'prime' => 'prime',
            'sbm' => 'sbm', 'sidian' => 'sidian', 'spire' => 'spire', 'stanbic' => 'stanbic', 'standard chartered' => 'stanchart',
            'uba' => 'uba', 'imbank' => 'imbank', 'coop' => 'coop'];
        foreach ($words as $w => $slug) {
            if (str_contains($n, $w)) {
                return $slug;
            }
        }

        return null;
    }
}
