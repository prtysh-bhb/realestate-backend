<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected $model = 'gemini-2.0-flash-exp'; // Working model

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    protected function getHttpClient()
    {
        return Http::timeout(30)->withoutVerifying();
    }

    public function generateText(string $prompt)
    {
        try {
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = $this->getHttpClient()->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'success' => true,
                'text' => $text,
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini generateText error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateJSON(string $prompt, int $retries = 2)
    {
        try {
            $fullPrompt = $prompt . "\n\nIMPORTANT: Respond ONLY with valid JSON. No markdown, no explanation, no code blocks. Just pure JSON.";
            
            $response = $this->generateText($fullPrompt);

            if (!$response['success']) {
                throw new \Exception($response['error']);
            }

            $text = $response['text'];
            
            $text = preg_replace('/```json\s*/s', '', $text);
            $text = preg_replace('/```\s*/s', '', $text);
            $text = trim($text);

            $json = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($retries > 0) {
                    Log::warning('Gemini JSON parse failed, retrying');
                    sleep(1);
                    return $this->generateJSON($prompt, $retries - 1);
                }
                
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return [
                'success' => true,
                'data' => $json,
                'raw_text' => $text,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function chat(string $message, array $history = [])
    {
        try {
            $contents = [];
            
            foreach ($history as $msg) {
                $contents[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'model',
                    'parts' => [
                        ['text' => $msg['content']]
                    ]
                ];
            }
            
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $message]
                ]
            ];

            $response = $this->getHttpClient()->post(
                "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.9,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            );

            if ($response->failed()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $text];

            return [
                'success' => true,
                'text' => $text,
                'history' => $history,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}