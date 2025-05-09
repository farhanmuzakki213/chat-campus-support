<?php

namespace App\Livewire;

use App\Models\ChatLog;
use Livewire\Component;
use App\Models\ChatSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatApp extends Component
{
    public string|null $activeSessionId = null;
    public $sessions = [];
    public $messages = [];
    public $newMessage = '';

    protected $listeners = ['refreshSessions' => 'refreshChatList'];

    public function mount()
    {
        $this->refreshChatList();
    }

    public function getActiveSessionProperty()
    {
        if (!$this->activeSessionId) return null;

        return ChatSession::with('logs')
            ->where('user_id', Auth::id())
            ->where('session_id', $this->activeSessionId)
            ->first();
    }

    public function sendMessage()
    {
        if (!$this->activeSession) {
            $this->addError('error', 'Tidak ada sesi aktif. Silakan mulai sesi baru.');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Simpan pertanyaan user
            $log = ChatLog::create([
                'session_id' => $this->activeSession->session_id,
                'user_id' => auth()->id(),
                'question' => $this->newMessage,
                'source' => 'pending',
            ]);

            // Siapkan variabel jawaban
            $answer = null;
            $source = 'knowledge_base';
            $attachments = [];

            // 2. Kirim ke API lokal
            $local = Http::post('https://1507-103-190-46-86.ngrok-free.app/ask', [
                'question' => $this->newMessage,
            ]);

            if ($local->ok() && isset($local['answer'])) {
                $answerLokal = strtolower($local['answer']);
                $unknown = collect(['maaf', 'belum menemukan', 'tidak tahu', 'tidak memiliki']);

                $isUnknown = $unknown->contains(fn($word) => str_contains($answerLokal, $word));

                if (!$isUnknown) {
                    $answer = $local['answer'];
                    $source = $local['source'] ?? 'knowledge_base';
                    $attachments = $local['attachments'] ?? [];
                }
            }

            // 3. Fallback ke Gemini jika lokal tidak memadai
            if (empty($answer)) {
                $gemini = $this->askGemini($this->newMessage);

                if ($gemini) {
                    $answer = $gemini;
                    $source = 'gemini';
                } else {
                    $answer = 'Maaf, server sedang sibuk. Silakan coba lagi nanti.';
                    $source = 'fallback';
                }
            }
            // dd($answer, $source, $attachments);
            // 4. Update log dengan jawaban
            // dd($log);
            $log->update([
                'answer' => $answer,
                'source' => $source,
            ]);

            // 5. Kirim ke user
            $this->messages[] = (object) [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'question' => $log->question,
                'answer' => (string) $log->answer,
                'source' => (string) $log->source,
                'created_at' => now()->toDateTimeString(),
                'attachments' => $attachments ?? [],
                ];

            // Reset input
            $this->dispatch('scroll-to-bottom');
            $this->newMessage = '';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('error', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    // Panggil Gemini API publik (gratisan)
    protected function askGemini(string $question): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=API_KEY_KAMU', [
                'contents' => [[
                    'parts' => [['text' => $question]]
                ]]
            ]);
            return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            logger('Gemini error: ' . $e->getMessage());
            return null;
        }
    }


    public function startNewSession()
    {
        try {
            $newSession = ChatSession::create([
                'session_id' => Str::uuid(),
                'user_id' => Auth::id(),
                'started_at' => now(),
            ]);
            // dd("test");
            $this->activeSessionId = $newSession->session_id;
            $this->messages = [];

            $this->refreshChatList();

            $this->dispatch('new-session-created');
            $this->dispatch('scroll-to-bottom');
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Gagal membuat sesi baru: ' . $e->getMessage());
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
        $this->activeSessionId = $sessionId;
        $this->messages = ChatLog::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($log) => (object) [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'question' => $log->question,
                'answer' => $log->answer,
                'source' => $log->source,
                'created_at' => $log->created_at->toDateTimeString(),
                'attachments' => $log->attachments ?? [],
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.chat-app')->layout('layouts.app');
    }
}
