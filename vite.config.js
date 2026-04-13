import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

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
        alias: {
            '@': '/resources/js',
        },
    },

})
