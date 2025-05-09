@extends('layouts.app')
@section('content')
    <!-- resources/views/livewire/chat-app.blade.php -->
    <div class="flex h-screen bg-gray-50" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <div class="flex flex-col w-64 bg-white border-r border-gray-200 transition-all duration-300 fixed md:relative h-full"
            :class="{ '-ml-64': !sidebarOpen, 'ml-0': sidebarOpen }">

            <!-- New Chat Button -->
            <button wire:click="startNewSession"
                class="mx-4 my-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                + New Chat
            </button>

            <!-- Chat History -->
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 py-2 text-sm font-medium text-gray-500">Recent Chats</div>
                <ul class="space-y-1">
                    @foreach ($sessions as $session)
                        <li>
                            <button wire:click="loadSession('{{ $session->session_id }}')"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 flex items-center justify-between"
                                :class="{
                                    'bg-gray-100': $wire.activeSession && $wire.activeSession - >
                                        session_id === '{{ $session->session_id }}'
                                }">
                                <span class="truncate">
                                    {{ $session->logs->first()->question ?? 'New Chat' }}
                                </span>
                                <span class="text-xs text-gray-400 ml-2">
                                    {{ $session->started_at->format('M d') }}
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
                    @if ($activeSession)
                        Chat session from {{ $activeSession->started_at->format('M d, Y') }}
                    @else
                        Start a new conversation
                    @endif
                </div>
            </header>

            <!-- Chat Messages -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4">
                <div class="max-w-3xl mx-auto space-y-6">
                    @if ($activeSession)
                        @foreach ($messages as $message)
                            <div class="{{ $message->user_id === auth()->id() ? 'pl-12' : 'pr-12' }}">
                                <div
                                    class="{{ $message->user_id === auth()->id() ? 'bg-purple-600 text-white' : 'bg-white shadow-sm' }} rounded-xl p-4">
                                    @if ($message->user_id === auth()->id())
                                        <div class="flex items-center justify-between text-xs mb-2 opacity-80">
                                            <span>You</span>
                                            <span>{{ $message->created_at->format('h:i A') }}</span>
                                        </div>
                                        <div class="text-sm">{{ $message->question }}</div>
                                    @else
                                        <div class="flex items-center justify-between text-xs mb-2 text-gray-500">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium">CampusBot</span>
                                                @if ($message->source === 'knowledge_base')
                                                    <span
                                                        class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Verified</span>
                                                @endif
                                            </div>
                                            <span>{{ $message->created_at->format('h:i A') }}</span>
                                        </div>
                                        <div class="text-sm text-gray-800 space-y-3">
                                            {!! nl2br(e($message->answer)) !!}

                                            @if ($message->attachments)
                                                <div class="mt-4 space-y-2">
                                                    @foreach ($message->attachments as $attachment)
                                                        <a href="{{ $attachment['url'] }}"
                                                            class="flex items-center space-x-2 text-purple-600 hover:text-purple-800">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <span class="text-sm">{{ $attachment['name'] }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
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

            <!-- Input Area -->
            @if ($activeSession)
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
@endsection
