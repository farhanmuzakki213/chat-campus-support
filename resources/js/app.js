import './bootstrap';
import 'alpinejs'


window.addEventListener('DOMContentLoaded', () => {
    Livewire.on('processQuestion', (logId) => {
        fetch(`/chat/process/${logId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Livewire.emit('newMessageSent');

                    // Jika ada lampiran, tampilkan notifikasi
                    if (data.attachments && data.attachments.length > 0) {
                        const attachmentNames = data.attachments.map(a => a.name).join(', ');
                        alert(`Jawaban dilengkapi dengan lampiran: ${attachmentNames}`);
                    }
                }
            });
    });
});

