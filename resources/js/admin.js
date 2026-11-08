// Skrip halaman admin & login. Gayanya ada di resources/css/admin-app.css (dimuat sebagai <link>).
import * as bootstrap from 'bootstrap';
import { setupSongPicker } from './song-picker.js';

window.bootstrap = bootstrap;
setupSongPicker();

// Konfirmasi seragam untuk aksi berbahaya: <form data-confirm="Hapus ...?">
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset?.confirm;
    if (msg && !window.confirm(msg)) e.preventDefault();
});

// Cegah klik ganda pada tombol simpan (formulir terkirim dua kali)
document.addEventListener('submit', (e) => {
    if (e.defaultPrevented) return;
    const btn = e.submitter;
    if (btn && btn.dataset.noLock === undefined) {
        setTimeout(() => { btn.disabled = true; btn.classList.add('disabled'); }, 0);
    }
});

// Keluar: hapus salinan offline halaman Kontrol/Layar Live (berisi data yang perlu login)
document.addEventListener('submit', (e) => {
    if (String(e.target.action || '').endsWith('/logout')) navigator.serviceWorker?.controller?.postMessage('clear-pages');
});
