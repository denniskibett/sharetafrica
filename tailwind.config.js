import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
             screens: {
                '2xsm': '375px',
                'xsm': '425px',
                'sm': '640px',
                'md': '768px',
                'lg': '1024px',
                'xl': '1280px',
                '2xl': '1536px',
            },

            spacing: {
                '6.5': '1.625rem',
                '7.5': '1.875rem',
                '9.5': '2.375rem',
                '10.5': '2.625rem',
                '11.5': '2.875rem',
                '12.5': '3.125rem',
                '13.5': '3.375rem',
                '14.5': '3.625rem',
                '15.5': '3.875rem',
                '16.5': '4.125rem',
                '17.5': '4.375rem',
                '18.5': '4.625rem',
                '19.5': '4.875rem',
                '20.5': '5.125rem',
            },

            fontFamily: {
                outfit: ['Outfit', ...defaultTheme.fontFamily.sans],
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },

            fontSize: {
                'theme-xs': ['12px', { lineHeight: '18px' }],
                'theme-sm': ['14px', { lineHeight: '20px' }],
                'theme-md': ['16px', { lineHeight: '24px' }],
                'theme-lg': ['18px', { lineHeight: '28px' }],
                'theme-xl': ['20px', { lineHeight: '30px' }],
                'theme-2xl': ['24px', { lineHeight: '32px' }],
                'theme-3xl': ['30px', { lineHeight: '38px' }],
                'theme-4xl': ['36px', { lineHeight: '44px' }],
                'theme-5xl': ['48px', { lineHeight: '60px' }],
            },

            colors: {
                brand: {
                    50: '#F7FFD6',
                    100: '#EEFFAD',
                    200: '#E5FF85',
                    300: '#DCFF5C',
                    400: '#D7FF49',
                    500: '#D4FF3D',
                    600: '#B8E600',
                    700: '#8FAF00',
                    800: '#667D00',
                    900: '#3D4B00',
                    950: '#202800',
                },

                'gray-dark': '#1A1A1A',

                success: {
                    50: '#ECFDF3',
                    100: '#D1FADF',
                    200: '#A6F4C5',
                    300: '#6CE9A6',
                    400: '#32D583',
                    500: '#12B76A',
                    600: '#039855',
                    700: '#027A48',
                    800: '#05603A',
                    900: '#054F31',
                },

                error: {
                    50: '#FFEEEE',
                    100: '#FACDCD',
                    200: '#F29B9B',
                    300: '#E66A6A',
                    400: '#D64545',
                    500: '#BA2525',
                    600: '#A61B1B',
                    700: '#911111',
                    800: '#780A0A',
                    900: '#610404',
                },

                warning: {
                    50: '#FFFAEB',
                    100: '#FEF0C7',
                    200: '#FEDF89',
                    300: '#FEC84B',
                    400: '#FEAA2C',
                    500: '#FD7A14',
                    600: '#F55D0E',
                    700: '#D63E00',
                    800: '#B22E00',
                    900: '#8C1D00',
                },  
                        
                alert: {
                    50: '#FDF3F0',
                    100: '#FBE5E2',
                    200: '#F7C6BC',
                    300: '#F39D8B',
                    400: '#EE6B55',
                    500: '#E53935',
                    600: '#C50E1A',
                    700: '#A70B14',
                    800: '#87070E',
                    900: '#6B050A',
                },
            },

            boxShadow: {
                'theme-xs': '0px 1px 2px 0px rgba(16, 24, 40, 0.05)',
                'theme-sm': '0px 1px 2px 0px rgba(16, 24, 40, 0.05)',
                'theme-md': '0px 4px 8px -2px rgba(16, 24, 40, 0.10), 0px 2px 4px -2px rgba(16, 24, 40, 0.06)',
                'theme-lg': '0px 12px 16px -4px rgba(16, 24, 40, 0.08), 0px 4px 6px -2px rgba(16, 24, 40, 0.03)',
                'theme-xl': '0px 20px 24px -4px rgba(16, 24, 40, 0.08), 0px 8px 8px -4px rgba(16, 24, 40, 0.03)',

                'slider-navigation': '0px 1px 2px 0px rgba(16, 24, 40, 0.05)',
                'slider-navigation-md': '0px 4px 8px -2px rgba(16, 24, 40, 0.10), 0px 2px 4px -2px rgba(16, 24, 40, 0.06)',
                'slider-navigation-lg': '0px 12px 16px -4px rgba(16, 24, 40, 0.08), 0px 4px 6px -2px rgba(16, 24, 40, 0.03)',
                'slider-navigation-xl': '0px 20px 24px -4px rgba(16, 24, 40, 0.08), 0px 8px 8px -4px rgba(16, 24, 40, 0.03)',

                'datepicker': '0px 4px 8px -2px rgba(16, 24, 40, 0.10), 0px 2px 4px -2px rgba(16, 24, 40, 0.06)',
                'datepicker-lg': '0px 12px 16px -4px rgba(16, 24, 40, 0.08), 0px 4px 6px -2px rgba(16, 24, 40, 0.03)',
                'datepicker-xl': '0px 20px 24px -4px rgba(16, 24, 40, 0.08), 0px 8px 8px -4px rgba(16, 24, 40, 0.03)',
                'datepicker-2xl': '0px 24px 32px -4px rgba(16, 24, 40, 0.08), 0px 12px 12px -4px rgba(16, 24, 40, 0.03)',
                'datepicker-3xl': '0px 28px 36px -4px rgba(16, 24, 40, 0.08), 0px 16px 16px -4px rgba(16, 24, 40, 0.03)',

                'shadow-theme-xs': '0px 1px 2px 0px rgba(16, 24, 40, 0.05)',
                'shadow-theme-sm': '0px 1px 2px 0px rgba(16, 24, 40, 0.05)',
                'shadow-theme-md': '0px 4px 8px -2px rgba(16, 24, 40, 0.10), 0px 2px 4px -2px rgba(16, 24, 40, 0.06)',
                'shadow-theme-lg': '0px 12px 16px -4px rgba(16, 24, 40, 0.08), 0px 4px 6px -2px rgba(16, 24, 40, 0.03)',
                'shadow-theme-xl': '0px 20px 24px -4px rgba(16, 24, 40, 0.08), 0px 8px 8px -4px rgba(16, 24, 40, 0.03)',
                'shadow-theme-2xl': '0px 24px 32px -4px rgba(16, 24, 40, 0.08), 0px 12px 12px -4px rgba(16, 24, 40, 0.03)',
                'shadow-theme-3xl': '0px 28px 36px -4px rgba(16, 24, 40, 0.08), 0px 16px 16px -4px rgba(16, 24, 40, 0.03)',
            },
        },
    },

    plugins: [forms],
};