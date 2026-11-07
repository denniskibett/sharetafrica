<?php

namespace App\Support;

/**
 * The Android app that is currently offered for download, read from android.json written by the app's
 * release script: {"version":"1.0.0","build":1,"released_at":"…","files":{"arm64":{"file":"…","size":…,"sha256":"…"},…}}.
 * Null when no APK has been uploaded yet, so the site simply shows no Android download.
 */
class AndroidRelease
{
    public const DEFAULT_ABI = 'arm64';

    public static function latest(): ?array
    {
        static $cache = [];
        $dir = config('mobile.android_dir');
        if (array_key_exists($dir, $cache)) {
            return $cache[$dir];
        }
        $json = is_file("$dir/android.json") ? json_decode((string) file_get_contents("$dir/android.json"), true) : null;
        $ok = is_array($json) && isset($json['version'], $json['files'][self::DEFAULT_ABI]['file'])
            && is_file($dir.'/'.basename($json['files'][self::DEFAULT_ABI]['file']));

        return $cache[$dir] = $ok ? $json : null;
    }

    /** Full path of the APK for a phone type, or null. Only file names from android.json, never from the URL. */
    public static function path(string $abi): ?string
    {
        $file = self::latest()['files'][$abi]['file'] ?? null;
        $path = $file ? config('mobile.android_dir').'/'.basename($file) : null;

        return $path && is_file($path) ? $path : null;
    }

    /** "19 MB" for the badge. */
    public static function size(string $abi = self::DEFAULT_ABI): string
    {
        return round(((int) (self::latest()['files'][$abi]['size'] ?? 0)) / 1048576).' MB';
    }
}
