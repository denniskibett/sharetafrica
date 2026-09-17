import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/styles.css',
                'resources/js/site.js',
            ],
            refresh: true,
        }),
    ],

    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',

        rollupOptions: {
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/styles.css',
                'resources/js/site.js',
            ],
        },
    },
});