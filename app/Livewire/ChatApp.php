<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatSession;
use App\Models\ChatLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChatApp extends Component
{
    public $activeSession;
    public $sidebarOpen = true;
    public $newMessage = '';
    public $messages = [];
    public $sessions = [];
    public $showNotifications = false;
    public $unreadNotifications = 0;
    public $relatedEvents = [];

    protected $listeners = [
        'loadSession',
        'newMessageSent',
        'loadDepartmentEvents' => 'loadDepartmentEvents',
        'toggleNotifications' => 'toggleNotifications'
    ];

    public function mount()
    {
        $this->sessions = ChatSession::where('user_id', auth()->id())
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function startNewSession()
    {
        $this->activeSession = ChatSession::create([
            'session_id' => Str::uuid(),
            'user_id' => auth()->id()
        ]);

        $this->sessions = ChatSession::where('user_id', auth()->id())
            ->orderBy('started_at', 'desc')
            ->get();

        $this->messages = [];
    }

    public function loadSession($sessionId)
    {
        $this->activeSession = ChatSession::find($sessionId);
        $this->messages = ChatLog::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function loadDepartmentEvents($deptId)
    {
        $this->relatedEvents = Event::where('dept_id', $deptId)
            ->upcoming()
            ->get()
            ->toArray();
    }

    public function toggleNotifications()
    {
        $this->showNotifications = !$this->showNotifications;
        if (!$this->showNotifications) return;

        $this->unreadNotifications = 0;
        Notification::where('user_id', auth()->id())
            ->where('read_at', null)
            ->update(['read_at' => now()]);
    }

    public function getAttachmentsProperty()
    {
        if (!$this->activeSession) return [];

        return $this->activeSession->logs()
            ->whereNotNull('knowledge_id')
            ->with('attachments')
            ->get()
            ->pluck('attachments')
            ->flatten()
            ->unique('url');
    }
    public function sendMessage()
    {
        if (trim($this->newMessage) === '') return;

        // Simpan pertanyaan user
        $log = ChatLog::create([
            'session_id' => $this->activeSession->session_id,
            'user_id' => auth()->id(),
            'question' => $this->newMessage,
            'source' => 'ai_generated' // Default, akan diupdate jika ditemukan di knowledge base
        ]);

        $this->messages->push($log);
        $this->newMessage = '';

        // Proses pencarian jawaban (akan diimplementasikan nanti dengan MongoDB)
        $this->emit('processQuestion', $log->log_id);
    }

    public function render()
    {
        $this->sessions = ChatSession::where('user_id', auth()->id())
            ->withCount('logs')
            ->orderBy('started_at', 'desc')
            ->get();

        return view('livewire.chat-app', [
            'sessions' => $this->sessions,
            'activeSession' => $this->activeSession,
            'messages' => $this->messages ?? []
        ])->extends('layouts.app');
    }
}
