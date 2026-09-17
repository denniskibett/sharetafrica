{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ SystemHelper::appName() }} | {{ ucfirst(request()->segment(1) ?? 'Dashboard') }}</title>

    {{-- Dynamic Meta --}}
    @if(SystemHelper::metaDescription())
        <meta name="description" content="{{ SystemHelper::metaDescription() }}">
    @endif

    @if(SystemHelper::metaKeywords())
        <meta name="keywords" content="{{ SystemHelper::metaKeywords() }}">
    @endif

    {{-- Dynamic Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="apple-touch-icon" href="{{ SystemHelper::faviconUrl() }}">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        :root {
            --primary-color: {{ SystemHelper::primaryColor() }};
            --secondary-color: {{ SystemHelper::secondaryColor() }};

            --primary-rgb: {{ implode(',', sscanf(SystemHelper::primaryColor(), "#%02x%02x%02x")) }};
            --secondary-rgb: {{ implode(',', sscanf(SystemHelper::secondaryColor(), "#%02x%02x%02x")) }};

            --primary-10: rgba(var(--primary-rgb), 0.1);
            --primary-20: rgba(var(--primary-rgb), 0.2);
            --primary-30: rgba(var(--primary-rgb), 0.3);
            --primary-40: rgba(var(--primary-rgb), 0.4);
            --primary-50: rgba(var(--primary-rgb), 0.5);
            --primary-60: rgba(var(--primary-rgb), 0.6);
            --primary-70: rgba(var(--primary-rgb), 0.7);
            --primary-80: rgba(var(--primary-rgb), 0.8);
            --primary-90: rgba(var(--primary-rgb), 0.9);

            --secondary-10: rgba(var(--secondary-rgb), 0.1);
            --secondary-20: rgba(var(--secondary-rgb), 0.2);
            --secondary-30: rgba(var(--secondary-rgb), 0.3);
            --secondary-40: rgba(var(--secondary-rgb), 0.4);
            --secondary-50: rgba(var(--secondary-rgb), 0.5);
            --secondary-60: rgba(var(--secondary-rgb), 0.6);
            --secondary-70: rgba(var(--secondary-rgb), 0.7);
            --secondary-80: rgba(var(--secondary-rgb), 0.8);
            --secondary-90: rgba(var(--secondary-rgb), 0.9);

            --bg-primary-light: rgba(var(--primary-rgb), 0.1);
            --bg-primary-soft: rgba(var(--primary-rgb), 0.15);
            --bg-primary-medium: rgba(var(--primary-rgb), 0.2);

            --primary-hover: rgba(var(--primary-rgb), 0.9);
            --secondary-hover: rgba(var(--secondary-rgb), 0.9);
            --primary-dark: rgba(var(--primary-rgb), 0.8);

            --success-color: #10b981;
            --success-rgb: 16, 185, 129;
            --success-50: rgba(var(--success-rgb), 0.1);
            --success-100: rgba(var(--success-rgb), 0.2);
            --success-200: rgba(var(--success-rgb), 0.3);
            --success-300: rgba(var(--success-rgb), 0.4);
            --success-400: rgba(var(--success-rgb), 0.5);
            --success-500: var(--success-color);
            --success-600: #059669;

            --error-color: #ef4444;
            --error-rgb: 239, 68, 68;
            --error-50: rgba(var(--error-rgb), 0.1);
            --error-100: rgba(var(--error-rgb), 0.2);
            --error-200: rgba(var(--error-rgb), 0.3);
            --error-300: rgba(var(--error-rgb), 0.4);
            --error-400: rgba(var(--error-rgb), 0.5);
            --error-500: var(--error-color);
            --error-600: #dc2626;

            --warning-color: #f59e0b;
            --warning-rgb: 245, 158, 11;
            --warning-50: rgba(var(--warning-rgb), 0.1);
            --warning-100: rgba(var(--warning-rgb), 0.2);
            --warning-200: rgba(var(--warning-rgb), 0.3);
            --warning-300: rgba(var(--warning-rgb), 0.4);
            --warning-400: rgba(var(--warning-rgb), 0.5);
            --warning-500: var(--warning-color);
            --warning-600: #d97706;
        }

        /* Primary color utility classes */
        .bg-primary { background-color: var(--primary-color); }
        .bg-primary-10 { background-color: var(--primary-10); }
        .bg-primary-20 { background-color: var(--primary-20); }
        .bg-primary-30 { background-color: var(--primary-30); }
        .bg-primary-40 { background-color: var(--primary-40); }
        .bg-primary-50 { background-color: var(--primary-50); }
        .bg-primary-60 { background-color: var(--primary-60); }
        .bg-primary-70 { background-color: var(--primary-70); }
        .bg-primary-80 { background-color: var(--primary-80); }
        .bg-primary-90 { background-color: var(--primary-90); }

        .bg-primary-light { background-color: var(--bg-primary-light); }
        .bg-primary-soft { background-color: var(--bg-primary-soft); }
        .bg-primary-medium { background-color: var(--bg-primary-medium); }

        .text-primary { color: var(--primary-color); }
        .text-primary-10 { color: var(--primary-10); }
        .text-primary-20 { color: var(--primary-20); }
        .text-primary-30 { color: var(--primary-30); }
        .text-primary-40 { color: var(--primary-40); }
        .text-primary-50 { color: var(--primary-50); }
        .text-primary-60 { color: var(--primary-60); }
        .text-primary-70 { color: var(--primary-70); }
        .text-primary-80 { color: var(--primary-80); }
        .text-primary-90 { color: var(--primary-90); }

        .border-primary { border-color: var(--primary-color); }
        .border-primary-10 { border-color: var(--primary-10); }
        .border-primary-20 { border-color: var(--primary-20); }
        .border-primary-30 { border-color: var(--primary-30); }
        .border-primary-40 { border-color: var(--primary-40); }
        .border-primary-50 { border-color: var(--primary-50); }
        .border-primary-60 { border-color: var(--primary-60); }
        .border-primary-70 { border-color: var(--primary-70); }
        .border-primary-80 { border-color: var(--primary-80); }
        .border-primary-90 { border-color: var(--primary-90); }

        /* Secondary color utility classes */
        .bg-secondary { background-color: var(--secondary-color); }
        .bg-secondary-10 { background-color: var(--secondary-10); }
        .bg-secondary-20 { background-color: var(--secondary-20); }
        .bg-secondary-30 { background-color: var(--secondary-30); }
        .bg-secondary-40 { background-color: var(--secondary-40); }
        .bg-secondary-50 { background-color: var(--secondary-50); }
        .bg-secondary-60 { background-color: var(--secondary-60); }
        .bg-secondary-70 { background-color: var(--secondary-70); }
        .bg-secondary-80 { background-color: var(--secondary-80); }
        .bg-secondary-90 { background-color: var(--secondary-90); }

        .text-secondary { color: var(--secondary-color); }
        .text-secondary-10 { color: var(--secondary-10); }
        .text-secondary-20 { color: var(--secondary-20); }
        .text-secondary-30 { color: var(--secondary-30); }
        .text-secondary-40 { color: var(--secondary-40); }
        .text-secondary-50 { color: var(--secondary-50); }
        .text-secondary-60 { color: var(--secondary-60); }
        .text-secondary-70 { color: var(--secondary-70); }
        .text-secondary-80 { color: var(--secondary-80); }
        .text-secondary-90 { color: var(--secondary-90); }

        .border-secondary { border-color: var(--secondary-color); }
        .border-secondary-10 { border-color: var(--secondary-10); }
        .border-secondary-20 { border-color: var(--secondary-20); }
        .border-secondary-30 { border-color: var(--secondary-30); }
        .border-secondary-40 { border-color: var(--secondary-40); }
        .border-secondary-50 { border-color: var(--secondary-50); }
        .border-secondary-60 { border-color: var(--secondary-60); }
        .border-secondary-70 { border-color: var(--secondary-70); }
        .border-secondary-80 { border-color: var(--secondary-80); }
        .border-secondary-90 { border-color: var(--secondary-90); }

        /* Hover classes */
        .hover\:bg-primary:hover { background-color: var(--primary-color); filter: brightness(0.95); }
        .hover\:bg-primary-10:hover { background-color: var(--primary-10); }
        .hover\:bg-primary-20:hover { background-color: var(--primary-20); }
        .hover\:bg-primary-30:hover { background-color: var(--primary-30); }
        .hover\:bg-primary-40:hover { background-color: var(--primary-40); }
        .hover\:bg-primary-50:hover { background-color: var(--primary-50); }
        .hover\:bg-primary-60:hover { background-color: var(--primary-60); }
        .hover\:bg-primary-70:hover { background-color: var(--primary-70); }
        .hover\:bg-primary-80:hover { background-color: var(--primary-80); }
        .hover\:bg-primary-90:hover { background-color: var(--primary-90); }

        .hover\:text-primary:hover { color: var(--primary-color); }
        .hover\:border-primary:hover { border-color: var(--primary-color); }

        .hover\:bg-secondary:hover { background-color: var(--secondary-color); filter: brightness(0.95); }
        .hover\:text-secondary:hover { color: var(--secondary-color); }
        .hover\:border-secondary:hover { border-color: var(--secondary-color); }

        /* Button variations */
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-90);
            filter: brightness(0.95);
        }
        .btn-primary:active {
            background-color: var(--primary-80);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background-color: var(--secondary-90);
            filter: brightness(0.95);
        }

        .btn-outline-primary {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
            transition: all 0.2s ease;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-10);
            border-color: var(--primary-80);
        }

        .btn-outline-secondary {
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background-color: transparent;
            transition: all 0.2s ease;
        }
        .btn-outline-secondary:hover {
            background-color: var(--secondary-10);
            border-color: var(--secondary-80);
        }

        .link-primary {
            color: var(--primary-color);
            transition: color 0.2s ease;
        }
        .link-primary:hover {
            color: var(--primary-80);
        }

        .badge-primary {
            background-color: var(--bg-primary-soft);
            color: var(--primary-color);
            border: 1px solid var(--primary-20);
        }

        .badge-secondary {
            background-color: var(--secondary-10);
            color: var(--secondary-color);
            border: 1px solid var(--secondary-20);
        }

        .focus-ring-primary:focus {
            outline: none;
            ring: 2px solid var(--primary-50);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-70));
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-70));
        }

        /* Status classes */
        .bg-success-50 { background-color: var(--success-50); }
        .text-success-600 { color: var(--success-600); }

        .bg-error-50 { background-color: var(--error-50); }
        .text-error-600 { color: var(--error-600); }

        .bg-warning-50 { background-color: var(--warning-50); }
        .text-warning-600 { color: var(--warning-600); }

        .dark .bg-success-500\/15 { background-color: rgba(var(--success-rgb), 0.15); }
        .dark .text-success-500 { color: var(--success-500); }

        .dark .bg-error-500\/15 { background-color: rgba(var(--error-rgb), 0.15); }
        .dark .text-error-500 { color: var(--error-500); }

        .dark .bg-warning-500\/15 { background-color: rgba(var(--warning-rgb), 0.15); }
        .dark .text-orange-400 { color: #fb923c; }
    </style>

    {{-- Prevent flash of incorrect theme before Alpine loads --}}
    <script>
        (function () {
            try {
                const stored = JSON.parse(localStorage.getItem('darkMode') ?? 'false');
                if (stored === true) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) { /* noop */ }
        })();
    </script>
</head>

<body
    class="font-outfit antialiased bg-gray-100"
    x-data="appShell"
    :class="{ 'dark bg-gray-900': darkMode }"
>
    @include('partials.preloader')

    <div class="flex h-screen overflow-hidden">
        @include('partials.sidebar')

        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            @include('partials.header')

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- App shell state — declared via Alpine.data() so it loads after Alpine is ready --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appShell', () => ({
                page: @json(request()->segment(1) ?? 'dashboard'),
                loaded: true,
                darkMode: (() => {
                    try {
                        return JSON.parse(localStorage.getItem('darkMode') ?? 'false');
                    } catch (e) {
                        return false;
                    }
                })(),
                stickyMenu: false,
                sidebarToggle: false,
                scrollTop: false,
                selected: localStorage.getItem('selectedMenu')
                    ?? @json(ucfirst(request()->segment(1) ?? 'Dashboard')),

                init() {
                    this.$watch('darkMode', (value) => {
                        localStorage.setItem('darkMode', JSON.stringify(value));
                        document.documentElement.classList.toggle('dark', value === true);
                    });

                    this.$watch('selected', (value) => {
                        localStorage.setItem('selectedMenu', value);
                    });

                    // Sync initial DOM state with the loaded value
                    document.documentElement.classList.toggle('dark', this.darkMode === true);
                },
            }));
        });
    </script>
</body>
</html>