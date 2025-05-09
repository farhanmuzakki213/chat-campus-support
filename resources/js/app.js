import './bootstrap';

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
// Tambahkan di bagian paling atas
document.addEventListener('livewire:init', () => {
    Livewire.on('newMessageSent', () => {
        console.log('Livewire event received!');
    });

    Livewire.on('error', (message) => {
        console.error('Livewire error:', message);
    });
});

// Tambahkan listener untuk tombol
document.querySelector('[wire\\:click="startNewSession"]').addEventListener('click', function(e) {
    console.log('Tombol diklik!');
});

window.addEventListener('DOMContentLoaded', () => {
    Livewire.on('new-message', () => {
        const chatContainer = document.querySelector('.chat-messages');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });
});
