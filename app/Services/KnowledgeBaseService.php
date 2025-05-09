<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    public function search(string $query)
    {
        try {
            // Ambil semua data dari API
            $faqs = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(route('api.faqs'))
                ->json();

            $knowledge = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(route('api.knowledge-base'))
                ->json();

            // Cari yang paling relevan
            return $this->findBestMatch($query, $faqs, $knowledge);

        } catch (\Exception $e) {
            logger()->error('Search error: ' . $e->getMessage());
            return null;
        }
    }

    private function findBestMatch($query, $faqs, $knowledge)
    {
        $allResults = collect()
            ->merge($this->processFaqs($query, $faqs))
            ->merge($this->processKnowledge($query, $knowledge));

        return $allResults->sortByDesc('score')->first();
    }

    private function processFaqs($query, $faqs)
    {
        return collect($faqs)->map(function ($faq) use ($query) {
            return [
                'type' => 'faq',
                'score' => $this->calculateScore($query, $faq['question'] . ' ' . $faq['answer']),
                'data' => $faq
            ];
        });
    }

    private function processKnowledge($query, $knowledge)
    {
        return collect($knowledge)->map(function ($doc) use ($query) {
            return [
                'type' => 'knowledge',
                'score' => $this->calculateScore($query, $doc['title'] . ' ' . $doc['content']),
                'data' => $doc
            ];
        });
    }

    private function calculateScore($query, $text)
    {
        $queryWords = explode(' ', Str::lower($query));
        $textWords = explode(' ', Str::lower($text));

        $matchCount = count(array_intersect($queryWords, $textWords));
        $totalWords = count($queryWords);

        return $totalWords > 0 ? ($matchCount / $totalWords) * 100 : 0;
    }
}
