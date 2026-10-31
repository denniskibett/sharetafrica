<?php

namespace App\Integrations\IdReader;

use Illuminate\Support\Str;

/**
 * Fields from the OCR text of a Kenyan ID: the labelled front ("ID NUMBER", "FULL NAMES", "DATE OF BIRTH", "SEX";
 * Maisha cards use "SURNAME" / "GIVEN NAMES") and the machine-readable lines on the back (IDKYA…). Only confident
 * matches are returned; anything missing is left for the customer to fill in.
 */
final class KenyanIdText
{
    public static function parse(string $text): array
    {
        $text = strtoupper(str_replace("\r", '', $text));
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), 'strlen'));

        return array_filter(self::mrz($lines) + self::front($text, $lines));
    }

    private static function front(string $text, array $lines): array
    {
        $out = [];
        if (preg_match('/ID\s*(?:NO|NUMBER)\W{0,3}\s*([0-9]{7,9})\b/', $text, $m)) {
            $out['id_number'] = $m[1];
        }
        if (preg_match('/BIRTH\W*\s*([0-3]?\d)[.\/ -]([01]?\d)[.\/ -]((?:19|20)\d\d)/', $text, $m)) {
            $out['date_of_birth'] = self::date((int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/SEX\W*\s*(MALE|FEMALE)/', $text, $m)) {
            $out['sex'] = strtolower($m[1]);
        }
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'FULL NAMES') && isset($lines[$i + 1])) {
                $out['full_name'] = self::name($lines[$i + 1]);
            } elseif (str_contains($line, 'SURNAME') && isset($lines[$i + 1])) {
                $surname = self::name($lines[$i + 1]);
                $given = '';
                foreach (array_slice($lines, $i + 2, 3) as $j => $next) {
                    if (str_contains($next, 'GIVEN')) {
                        $given = self::name($lines[$i + 3 + $j] ?? '');
                    }
                }
                $out['full_name'] = trim($given.' '.$surname);
            }
        }

        return $out;
    }

    /** TD1 machine-readable zone: IDKYA… / YYMMDD?S… / SURNAME<<GIVEN<NAMES. */
    private static function mrz(array $lines): array
    {
        $mrz = array_values(array_filter(array_map(fn ($l) => str_replace([' ', '«'], ['', '<'], $l), $lines), fn ($l) => preg_match('/^[A-Z0-9<]{25,32}$/', $l) && str_contains($l, '<')));
        $start = collect($mrz)->search(fn ($l) => str_starts_with($l, 'IDKYA') || str_starts_with($l, 'IDKEN'));
        if ($start === false || ! isset($mrz[$start + 2])) {
            return [];
        }
        $out = [];
        if (preg_match('/^(\d{2})(\d{2})(\d{2})\d([MF<])/', $mrz[$start + 1], $m)) {
            $year = (int) $m[1] + ((int) $m[1] > (int) now()->format('y') ? 1900 : 2000);
            $out['date_of_birth'] = self::date($year, (int) $m[2], (int) $m[3]);
            $out['sex'] = ['M' => 'male', 'F' => 'female'][$m[4]] ?? null;
        }
        [$surname, $given] = array_pad(explode('<<', rtrim($mrz[$start + 2], '<'), 2), 2, '');
        if ($surname !== '') {
            $out['full_name'] = self::name(str_replace('<', ' ', $given).' '.str_replace('<', ' ', $surname));
        }

        return $out;
    }

    private static function name(string $raw): ?string
    {
        $name = trim(preg_replace('/\s+/', ' ', preg_replace('/[^A-Z\' -]/', '', $raw)));

        return strlen($name) >= 3 ? Str::title(strtolower($name)) : null;
    }

    private static function date(int $y, int $m, int $d): ?string
    {
        return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
    }
}
