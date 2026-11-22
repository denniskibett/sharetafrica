<?php

return [
    // The app asks /api/mobile/v1/config and should ask people to update below this version.
    'min_version' => env('MOBILE_MIN_VERSION', '1.0.0'),

    // Android APKs live OUTSIDE the release folders (default: /home/sharet.africa/downloads/android next to
    // public_html), so deploys and rollbacks never touch them. The app's scripts/release-android.sh uploads
    // the APKs and android.json there. See docs/v2/MOBILE-API.md.
    'android_dir' => env('MOBILE_ANDROID_DIR', dirname(base_path()).'/downloads/android'),

    // Payments approved with the phone's own lock (fingerprint, face or passcode) instead of the Sharet PIN: at most this
    // much a day per phone (cents; KES 150,000). Past it, the PIN. Owner, 22 Nov 2026: sign in with the PIN, then the phone lock.
    'phone_lock_daily_max' => (int) env('MOBILE_PHONE_LOCK_DAILY_MAX', 15_000_000),
];
