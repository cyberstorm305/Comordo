import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

const ddevUrl = process.env.DDEV_PRIMARY_URL;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    server: {
        ...(ddevUrl && {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            origin: `${ddevUrl}:5173`,
            cors: { origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(\.ddev\.site)(?::\d+)?$/ },
        }),
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    // The server bundle carries its dependencies, so the server needs Node but no node_modules.
    ssr: {
        noExternal: true,
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
