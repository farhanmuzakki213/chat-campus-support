<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatLog;
use App\Models\ChatSession;
use App\Services\KnowledgeBaseService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $knowledgeService;
    protected $geminiService;

    public function __construct(
        KnowledgeBaseService $knowledgeService,
        GeminiService $geminiService
    ) {
        $this->knowledgeService = $knowledgeService;
        $this->geminiService = $geminiService;
    }

    public function searchKnowledgeBase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->knowledgeService->search($request->question);

            if ($result && $result['score'] > 40) { // Threshold 40%
                $source = $result['type'] === 'faq' ? 'knowledge_base' : 'knowledge_base';
                $answer = $result['type'] === 'faq'
                    ? $result['data']['answer']
                    : $result['data']['content'];

                $log = $this->createChatLog(
                    $request->session_id,
                    $request->question,
                    $answer,
                    $source,
                    (string)$result['data']['_id']['$oid'],
                    $result['type'] === 'knowledge' ? $result['data']['attachments'] : []
                );

                return response()->json([
                    'status' => 'success',
                    'type' => $source,
                    'log' => $log,
                    'attachments' => $log->attachments
                ]);
            }

            // Jika tidak memenuhi threshold
            $aiResponse = $this->geminiService->generateResponse($request->question);

            $log = $this->createChatLog(
                $request->session_id,
                $request->question,
                $aiResponse,
                'ai_generated'
            );

            return response()->json([
                'status' => 'success',
                'type' => 'ai_generated',
                'log' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createChatLog($sessionId, $question, $answer, $source, $knowledgeId = null, $attachments = [])
    {
        return ChatLog::create([
            'session_id' => $sessionId,
            'user_id' => auth()->id(),
            'question' => $question,
            'answer' => $answer,
            'source' => $source,
            'knowledge_id' => $knowledgeId,
            'attachments' => $attachments
        ]);
    }
}
