@extends('layouts.app')
@section('content')
<div class="flex h-full">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 flex w-64 flex-col bg-gray-800 text-white transition-all duration-300 md:relative"
             :class="{ '-ml-64': !sidebarOpen, 'ml-0': sidebarOpen }">
            <!-- Tombol New Chat -->
            <button class="mx-4 my-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                + New Chat
            </button>

            <!-- Riwayat Chat -->
            <div class="flex-1 overflow-y-auto">
                <h3 class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Riwayat</h3>
                <ul>
                    <template x-for="chat in chats" :key="chat.id">
                        <li>
                            <button @click="activeChat = chat"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-700"
                                    :class="{ 'bg-gray-700': activeChat && activeChat.id === chat.id }">
                                <span x-text="chat.title"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Profile Section -->
            <div class="border-t border-gray-700 p-4">
                <button @click="showProfile = true" class="flex w-full items-center space-x-3">
                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                        <span class="text-xs font-medium">US</span>
                    </div>
                    <span class="text-sm font-medium">User Saya</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Header -->
            <header class="flex items-center justify-between border-b border-gray-200 bg-white p-4">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold" x-text="activeChat ? activeChat.title : 'Pilih atau buat chat baru'"></h1>
                <div class="w-6"></div> <!-- Spacer untuk balance -->
            </header>

            <!-- Chat Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4">
                <template x-if="activeChat">
                    <div class="space-y-4">
                        <template x-for="message in activeChat.messages" :key="message.id">
                            <div :class="{
                                'flex justify-end': message.sender === 'user',
                                'flex justify-start': message.sender === 'bot'
                            }">
                                <div :class="{
                                    'bg-indigo-600 text-white rounded-l-lg rounded-br-lg': message.sender === 'user',
                                    'bg-white border border-gray-200 rounded-r-lg rounded-bl-lg': message.sender === 'bot'
                                }" class="max-w-3xl px-4 py-2 shadow-sm">
                                    <p x-text="message.text"></p>
                                    <p class="text-xs mt-1 opacity-70" :class="{
                                        'text-indigo-200': message.sender === 'user',
                                        'text-gray-500': message.sender === 'bot'
                                    }" x-text="new Date(message.timestamp).toLocaleTimeString()"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!activeChat">
                    <div class="flex h-full items-center justify-center">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada chat aktif</h3>
                            <p class="mt-1 text-sm text-gray-500">Pilih chat dari sidebar atau buat chat baru</p>
                        </div>
                    </div>
                </template>
            </main>

            <!-- Input Area -->
            <div class="border-t border-gray-200 bg-white p-4">
                <form @submit.prevent="if(activeChat && newMessage.trim()) {
                    activeChat.messages.push({
                        id: Date.now(),
                        text: newMessage,
                        sender: 'user',
                        timestamp: new Date()
                    });
                    newMessage = '';
                 }" class="flex space-x-2">
                    <input x-model="newMessage" type="text" placeholder="Ketik pesan..."
                           class="flex-1 rounded-md border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                        Kirim
                    </button>
                </form>
            </div>
        </div>

        <!-- Profile Modal -->
        <div x-show="showProfile" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showProfile"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="showProfile = false"></div>

                <!-- Modal content -->
                <div x-show="showProfile"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:align-middle">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Profil Pengguna</h3>
                                <div class="mt-4">
                                    <div class="flex items-center justify-center">
                                        <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-2xl font-medium text-indigo-600">US</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nama</label>
                                            <p class="mt-1 text-sm text-gray-900">User Saya</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Email</label>
                                            <p class="mt-1 text-sm text-gray-900">user@example.com</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button"
                                @click="showProfile = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inisialisasi data dummy
        document.addEventListener('alpine:init', () => {
            Alpine.store('chats', [
                {
                    id: 1,
                    title: 'Pertanyaan tentang TALL Stack',
                    messages: [
                        {
                            id: 1,
                            text: 'Hai, bisa bantu saya dengan TALL Stack?',
                            sender: 'user',
                            timestamp: new Date(Date.now() - 3600000)
                        },
                        {
                            id: 2,
                            text: 'Tentu! TALL Stack adalah kombinasi dari Tailwind CSS, Alpine.js, Laravel, dan Livewire. Bagaimana saya bisa membantu?',
                            sender: 'bot',
                            timestamp: new Date(Date.now() - 3500000)
                        }
                    ]
                },
                {
                    id: 2,
                    title: 'Permintaan fitur baru',
                    messages: [
                        {
                            id: 1,
                            text: 'Saya ingin menambahkan fitur export ke PDF',
                            sender: 'user',
                            timestamp: new Date(Date.now() - 86400000)
                        }
                    ]
                }
            ]);

            Alpine.data('app', () => ({
                init() {
                    this.chats = Alpine.store('chats');
                    if (this.chats.length > 0) {
                        this.activeChat = this.chats[0];
                    }
                }
            }));
        });
    </script>
@endsection
