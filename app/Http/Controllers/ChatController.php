<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use Illuminate\Http\Request;
use App\Services\KnowledgeBaseService;

class ChatController extends Controller
{
    protected $knowledgeBaseService;

    public function __construct(KnowledgeBaseService $knowledgeBaseService)
    {
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

    public function processQuestion($logId)
    {
        $log = ChatLog::findOrFail($logId);

        // Cari di knowledge base (MongoDB)
        $knowledgeResult = $this->knowledgeBaseService->search($log->question);

        if ($knowledgeResult) {
            // Jika ditemukan di knowledge base
            $log->update([
                'answer' => $knowledgeResult['answer'],
                'source' => 'knowledge_base',
                'knowledge_id' => $knowledgeResult['id']
            ]);

            // Kirim lampiran jika ada
            $attachments = $knowledgeResult['attachments'] ?? [];
        } else {
            // Jika tidak ditemukan, generate jawaban AI
            $aiResponse = $this->generateAIResponse($log->question);

            $log->update([
                'answer' => $aiResponse,
                'source' => 'ai_generated'
            ]);

            $attachments = [];
        }

        return response()->json([
            'status' => 'success',
            'log' => $log,
            'attachments' => $attachments
        ]);
    }

    protected function generateAIResponse($question)
    {
        // Ini akan diimplementasikan dengan lebih baik nanti
        return "Saya sedang mempelajari pertanyaan Anda: \"$question\". Untuk sementara, Anda bisa mengecek informasi terkait di situs resmi kampus atau menghubungi bagian administrasi.";
    }
}
