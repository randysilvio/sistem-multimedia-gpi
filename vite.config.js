import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // CSS sebagai entri tersendiri: dimuat <link> di <head>, tidak ada kedip halaman tanpa gaya
            input: [
                'resources/css/admin-app.css',
                'resources/css/presenter-stage.css',
                'resources/css/control-panel.css',
                'resources/css/remote.css',
                'resources/js/admin.js',
                'resources/js/presenter/control.js',
                'resources/js/presenter/projector.js',
                'resources/js/import-pdf.js',
                'resources/js/presenter/remote.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Peringatan ukuran chunk hanya mengganggu: Bootstrap memang ±80 KB (gzip ±25 KB)
        chunkSizeWarningLimit: 700,
    },
});
