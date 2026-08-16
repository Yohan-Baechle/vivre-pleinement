import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/stripe-payment.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    subsets: ['latin'],
                    preload: [{ weight: 400 }, { weight: 500 }],
                }),
                bunny('Crimson Pro', {
                    weights: [400, 500, 600],
                    styles: ['normal', 'italic'],
                    subsets: ['latin'],
                    preload: [{ weight: 500 }, { weight: 400, style: 'italic' }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
