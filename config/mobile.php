<?php

return [
    // The app asks /api/mobile/v1/config and should ask people to update below this version.
    'min_version' => env('MOBILE_MIN_VERSION', '1.0.0'),

    // Android APKs live OUTSIDE the release folders (default: /home/sharet.africa/downloads/android next to
    // public_html), so deploys and rollbacks never touch them. The app's scripts/release-android.sh uploads
    // the APKs and android.json there. See docs/v2/MOBILE-API.md.
    'android_dir' => env('MOBILE_ANDROID_DIR', dirname(base_path()).'/downloads/android'),
];
