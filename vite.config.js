import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            // One stylesheet: Bootstrap plus the project's own CSS, compiled
            // from SCSS. Bootstrap used to be built separately and scoped under
            // `.bootstrap-scope` to keep its utility names away from Tailwind's;
            // with Tailwind gone there is nothing to keep it away from.
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('Manrope', {
                    weights: [500, 600, 700, 800],
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
