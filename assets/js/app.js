/**
 * Javascript Utama Aplikasi Buku Tamu Digital
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Jam Realtime jika elemen ada
    const liveClockEl = document.getElementById('liveClock');
    if (liveClockEl) {
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            liveClockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
        }, 1000);
    }

    // 2. Cascading Dropdown: Ruangan -> Pegawai
    const ruanganSelect = document.getElementById('ruangan_id');
    const pegawaiSelect = document.getElementById('pegawai_id');

    if (ruanganSelect && pegawaiSelect) {
        ruanganSelect.addEventListener('change', function () {
            const ruanganId = this.value;
            pegawaiSelect.innerHTML = '<option value="">-- Memuat Pegawai/Pejabat... --</option>';
            pegawaiSelect.disabled = true;

            if (!ruanganId) {
                pegawaiSelect.innerHTML = '<option value="">-- Pilih Ruangan Terlebih Dahulu --</option>';
                pegawaiSelect.disabled = true;
                return;
            }

            // Fetch via API
            const baseUrl = window.APP_BASE_URL || './';
            fetch(`${baseUrl}api/get_pegawai.php?ruangan_id=${ruanganId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        let html = '<option value="">-- Pilih Pejabat / Pegawai Tujuan --</option>';
                        data.forEach(pegawai => {
                            let icon = '🟢';
                            if (pegawai.status_ketersediaan === 'Sedang Rapat') icon = '🟡';
                            else if (pegawai.status_ketersediaan === 'Dinas Luar') icon = '🔴';
                            else if (pegawai.status_ketersediaan === 'Tidak di Tempat') icon = '⚪';

                            const avail = pegawai.status_ketersediaan ? ` [${icon} ${pegawai.status_ketersediaan}]` : '';
                            html += `<option value="${pegawai.id}">${pegawai.nama_pegawai} - ${pegawai.jabatan} ${avail}</option>`;
                        });
                        pegawaiSelect.innerHTML = html;
                        pegawaiSelect.disabled = false;
                    } else {
                        pegawaiSelect.innerHTML = '<option value="">(Tidak ada data staf khusus - Silakan lanjut)</option>';
                        pegawaiSelect.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Error fetching pegawai:', err);
                    pegawaiSelect.innerHTML = '<option value="">-- Gagal memuat data staf --</option>';
                });
        });
    }

    // 3. Conditional Input Jam Janji pada Form Tamu
    const statusJanjiRadios = document.querySelectorAll('input[name="status_janji"]');
    const jamJanjiContainer = document.getElementById('jamJanjiContainer');

    if (statusJanjiRadios.length > 0 && jamJanjiContainer) {
        statusJanjiRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'sudah_janji') {
                    jamJanjiContainer.classList.remove('d-none');
                } else {
                    jamJanjiContainer.classList.add('d-none');
                }
            });
        });
    }
});

/**
 * Play Audio Chime Notification (Web Audio API - No external file needed)
 */
function playChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
        notes.forEach((freq, index) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime + index * 0.12);
            gain.gain.setValueAtTime(0, ctx.currentTime + index * 0.12);
            gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + index * 0.12 + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + index * 0.12 + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime + index * 0.12);
            osc.stop(ctx.currentTime + index * 0.12 + 0.45);
        });
    } catch (e) {
        console.log('Audio chime error:', e);
    }
}
