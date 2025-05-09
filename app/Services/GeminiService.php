<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function generateResponse(string $prompt)
    {
        try {
            $response = Http::timeout(15)->post($this->apiUrl.'?key='.$this->apiKey, [
                'contents' => [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json()['candidates'][0]['content']['parts'][0]['text'];
            }

            Log::error('Gemini API error: '.$response->body());
            return "Maaf, saya tidak dapat menjawab pertanyaan tersebut saat ini.";

        } catch (\Exception $e) {
            Log::error('Gemini service error: '.$e->getMessage());
            return "Terjadi kesalahan dalam memproses permintaan Anda.";
        }
    }
}
