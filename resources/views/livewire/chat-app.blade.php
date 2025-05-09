<!-- resources/views/livewire/chat-app.blade.php -->
<div class="flex h-screen bg-gray-50" x-data="{ sidebarOpen: true }">
    <!-- Sidebar -->
    <div class="flex flex-col w-64 bg-white border-r border-gray-200 transition-all duration-300 fixed md:relative h-full"
        :class="{ '-ml-64': !sidebarOpen, 'ml-0': sidebarOpen }">
        <!-- New Chat Button -->
        <button wire:click="startNewSession" wire:loading.attr="disabled"
            class="mx-4 my-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors
                        disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove>+ New Chat</span>
            <span wire:loading>
                <svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </button>

        <!-- Chat History -->
        <div class="flex-1 overflow-y-auto">
            <div class="px-4 py-2 text-sm font-medium text-gray-500">Recent Chats</div>
            <ul class="space-y-1">
                @foreach ($sessions as $session)
                    <li>
                        <button wire:click="loadSession('{{ $session->session_id }}')"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 flex items-center justify-between
                                        {{ $this->activeSession && $this->activeSession->session_id === $session->session_id ? 'bg-gray-100' : '' }}
                                        @if ($loop->first) border-t border-gray-200 @endif">
                            <span class="truncate">
                                {{ $session->logs->first()->question ?? 'New Chat' }}
                            </span>
                            <span class="text-xs text-gray-400 ml-2">
                                {{-- {{ $session->started_at->format('M d') }} --}}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="border-t border-gray-200 p-4" x-data="{ showLogout: false }">
            <div class="relative">
                <!-- Profile Trigger -->
                <button @click="showLogout = !showLogout"
                    class="flex items-center space-x-3 w-full hover:bg-gray-50 rounded-lg p-2">
                    <div class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center text-white text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </button>

                <!-- Logout Popup -->
                <div x-show="showLogout" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute bottom-full left-0 mb-2 w-full origin-bottom-left z-50"
                    @click.away="showLogout = false">
                    <div class="bg-white rounded-lg shadow-lg p-2 border border-gray-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Log Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen">
        <!-- Header -->
        <header class="flex items-center justify-between border-b border-gray-200 bg-white p-4">
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex-1 text-center text-sm text-gray-600">
                @if ($this->activeSession)
                    Chat session from
                @else
                    Start a new conversation
                @endif
            </div>
        </header>

        <!-- Chat Messages -->
        <main class="chat-messages flex-1 overflow-y-auto bg-gray-50 p-4">
            <div class="max-w-3xl mx-auto space-y-6">
                @if ($this->activeSession)
                    @foreach ($messages as $log)
                        {{-- Tampilkan pertanyaan user --}}
                        @if (!empty($log->question))
                            <div class="flex justify-end">
                                <div class="max-w-md bg-purple-600 text-white p-4 rounded-xl mb-2">
                                    <div class="text-xs opacity-80 mb-1 flex justify-between">
                                        <span>You</span>
                                        <span>{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</span>
                                    </div>
                                    <div class="text-sm">
                                        {{ $log->question }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (!empty($log->answer))
                            <div class="flex justify-start">
                                <div class="max-w-md bg-white border text-gray-800 p-4 rounded-xl mb-4 shadow">
                                    <div class="text-xs text-gray-500 mb-1 flex justify-between">
                                        <span>CampusBot</span>
                                        <span>{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</span>
                                    </div>
                                    <div class="text-sm">
                                        {!! nl2br(e($log->answer)) !!}
                                    </div>

                                    {{-- Tampilkan label sumber --}}
                                    @if (!empty($log->source))
                                        <span
                                            class="mt-2 inline-block text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">
                                            {{ ucfirst($log->source) }}
                                        </span>
                                    @endif

                                    {{-- Lampiran --}}
                                    @if (!empty($log->attachments))
                                        <div class="mt-2 space-y-1">
                                            @foreach ($log->attachments as $attachment)
                                                <a href="{{ $attachment['url'] }}"
                                                    class="text-purple-600 text-sm hover:underline">
                                                    📎 {{ $attachment['name'] }}
                                                </a>    
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="h-full flex items-center justify-center">
                        <div class="text-center space-y-4">
                            <div class="text-2xl text-gray-400">🤖</div>
                            <div class="text-gray-500 text-sm">
                                Ask me anything about campus information
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        @if ($this->activeSession)
            <div class="border-t border-gray-200 bg-white p-4">
                <form wire:submit.prevent="sendMessage" class="max-w-3xl mx-auto relative">
                    <div class="relative">
                        <input wire:model="newMessage" type="text" placeholder="Message CampusBot..."
                            class="w-full pr-12 pl-4 py-3 border border-gray-300 rounded-xl focus:border-purple-500 focus:ring-purple-500 text-sm">

                        <button type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 text-center text-xs text-gray-500">
                        Example: "When is the academic calendar for 2024?"
                    </div>
                </form>
            </div>
        @endif

    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => { // Livewire v3 uses Alpine to listen for emitted
            events window.Livewire.on('scroll-to-bottom', () => {
                const chatContainer = document.querySelector('.chat-messages');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
            window.Livewire.on('show-error', (message) => {
                alert(message);
            });
            window.Livewire.on('new-session-created', () => {
                console.log('📥 Sesi baru berhasil dibuat.');
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
    </script>
@endpush

@push('styles')
    <style>
        .chat-messages {
            scroll-behavior: smooth;
            transition: scroll-top 0.3s ease-in-out;
        }
    </style>
@endpush
