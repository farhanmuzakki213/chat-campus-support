<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use MongoDB\Client as MongoClient;

class KnowledgeBaseService
{
    protected $mongoClient;
    protected $knowledgeBase;
    protected $faqs;

    public function __construct()
    {
        try {
            $this->mongoClient = new MongoClient(env('MONGODB_URI'));
            $db = $this->mongoClient->selectDatabase(env('MONGODB_DATABASE'));
            $this->knowledgeBase = $db->selectCollection('knowledge_base');
            $this->faqs = $db->selectCollection('faqs');
        } catch (\Exception $e) {
            Log::error('MongoDB connection error: ' . $e->getMessage());
        }
    }

    public function search(string $query)
    {
        if (!$this->knowledgeBase) {
            return null;
        }

        // Cari di FAQs terlebih dahulu
        $faqResult = $this->faqs->findOne([
            '$text' => ['$search' => $query]
        ], [
            'score' => ['$meta' => 'textScore'],
            'sort' => ['score' => ['$meta' => 'textScore']]
        ]);

        if ($faqResult) {
            return [
                'id' => (string)$faqResult['_id'],
                'answer' => $faqResult['answer'],
                'attachments' => []
            ];
        }

        // Cari di knowledge base
        $kbResult = $this->knowledgeBase->findOne([
            '$text' => ['$search' => $query]
        ], [
            'score' => ['$meta' => 'textScore'],
            'sort' => ['score' => ['$meta' => 'textScore']]
        ]);

        if ($kbResult) {
            $attachments = isset($kbResult['attachments']) ? array_map(function($attachment) {
                return [
                    'name' => $attachment['name'],
                    'url' => $attachment['url'],
                    'type' => $attachment['type']
                ];
            }, $kbResult['attachments']) : [];

            return [
                'id' => (string)$kbResult['_id'],
                'answer' => $kbResult['content'],
                'attachments' => $attachments
            ];
        }

        return null;
    }
}
