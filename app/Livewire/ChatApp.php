<?php

namespace App\Livewire;

use App\Models\ChatLog;
use Livewire\Component;
use App\Models\ChatSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChatApp extends Component
{
    public $activeSession = null;
    public $sessions = [];
    public $messages = [];
    public $newMessage = '';

    protected $listeners = ['refreshSessions' => 'refreshChatList'];

    public function mount()
    {
        $this->refreshChatList();
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string|max:1000']);
        try {
            // Simpan pertanyaan pengguna
            $log = ChatLog::create([
                'session_id' => $this->activeSession->session_id,
                'user_id' => auth()->id(),
                'question' => $this->newMessage,
                'source' => 'pending'
            ]);

            // Panggil API
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->post(route('api.chat.search'), [
                    'question' => $this->newMessage,
                    'session_id' => $this->activeSession->session_id
                ]);

            if ($response->successful()) {
                $this->messages->push($response->json()['log']);
                $this->dispatchBrowserEvent('new-message');
            } else {
                $this->addError('error', 'Gagal memproses pertanyaan');
            }

            $this->newMessage = '';
        } catch (\Exception $e) {
            $this->addError('error', 'Error: ' . $e->getMessage());
        }
    }

    public function startNewSession()
    {
        try {
            // Create new session
            $newSession = ChatSession::create([
                'session_id' => Str::uuid(),
                'user_id' => Auth::id(),
                'started_at' => now()
            ]);

            // Reset active session and messages
            $this->activeSession = $newSession;
            $this->messages = [];

            // Refresh session list
            $this->refreshChatList();

            // Scroll to bottom
            $this->dispatchBrowserEvent('new-session-created');
            $this->dispatchBrowserEvent('scroll-to-bottom');
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('show-error', [
                'message' => 'Gagal membuat sesi baru: ' . $e->getMessage()
            ]);
        }
    }

    private function refreshChatList()
    {
        $this->sessions = ChatSession::where('user_id', Auth::id())
            ->withCount('logs')
            ->orderByDesc('started_at')
            ->get();
    }

    public function loadSession($sessionId)
    {
        $this->activeSession = ChatSession::with('logs')
            ->findOrFail($sessionId);
        $this->messages = $this->activeSession->logs;
    }

    public function render()
    {
        return view('livewire.chat-app')->extends('layouts.app');
    }
}
