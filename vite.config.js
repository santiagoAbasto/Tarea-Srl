import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },

    plugins: [
        laravel({
            input: [
                'resources/js/app.jsx',
                'resources/js/web.js',
                'resources/css/admin/app.css',
                'resources/css/web/app.css',
            ],
            refresh: true,
        }),

        react(),
        tailwindcss(),

    ],

    resolve: {
        alias: [
            {
                find: 'use-sync-external-store/shim/with-selector',
                replacement: fileURLToPath(new URL('./node_modules/use-sync-external-store/cjs/use-sync-external-store-shim/with-selector.production.js', import.meta.url)),
            },
            {
                find: 'use-sync-external-store/shim',
                replacement: fileURLToPath(new URL('./node_modules/use-sync-external-store/cjs/use-sync-external-store-shim.production.js', import.meta.url)),
            },
            {
                find: '@',
                replacement: '/resources/js',
            },
        ],
    },

})
