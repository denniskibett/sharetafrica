<?php

namespace App\Http\Controllers;

use App\Support\AndroidRelease;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** /app/android → the Sharet APK for most phones (64-bit); /app/android/armv7 → older 32-bit phones. */
class AppDownloadController extends Controller
{
    public function android(string $abi = AndroidRelease::DEFAULT_ABI): BinaryFileResponse
    {
        abort_unless(in_array($abi, ['arm64', 'armv7'], true), 404);
        $path = AndroidRelease::path($abi);
        abort_unless($path !== null, 404);
        $version = AndroidRelease::latest()['version'];

        return response()->download($path, "Sharet-$version".($abi === 'armv7' ? '-32bit' : '').'.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache', // a new upload is picked up at once
        ]);
    }
}
