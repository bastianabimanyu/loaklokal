
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/css/login.css',
                'resources/css/register.css',
                'resources/css/navbar.css',
                'resources/css/barang-favorit.css',
                'resources/css/beranda.css',
                'resources/css/chat.css',
                'resources/css/chat-room.css',
                'resources/css/edit-akun.css',
                'resources/css/filter.css',
                'resources/css/profil.css',
                'resources/css/barang.css',
                'resources/css/detail-barang.css',

                'resources/js/beranda.js',
                'resources/js/chat.js',
                'resources/js/chat-room.js',
                'resources/js/filter.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
