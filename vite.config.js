import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/website.css',
                'resources/js/app.js',
                'resources/js/panel.js',   // Recruiter Panel entry point
                'resources/js/admin.js',   // Admin Panel entry point
                'resources/js/website.js', // Public Website SPA entry point
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@panel': fileURLToPath(new URL('./resources/js/panel', import.meta.url)),
            '@admin': fileURLToPath(new URL('./resources/js/admin', import.meta.url)),
            '@website': fileURLToPath(new URL('./resources/js/website', import.meta.url)),
        },
    },

    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-vue': ['vue', 'vue-router', 'pinia'],
                    'vendor-ui': ['@headlessui/vue', '@heroicons/vue'],
                },
            },
        },
        chunkSizeWarningLimit: 600,
    },

    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
