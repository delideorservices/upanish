<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;

class AIService
{
    protected $client;
    protected $apiUrl;
    
    public function __construct()
    {
        $this->apiUrl = SystemSetting::getValue('ai_service_url', 'http://127.0.0.1:5000');
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => SystemSetting::getValue('ai_timeout', 300),
        ]);
    }

    /**
     * Send homework to AI service for analysis.
     *
     * @param array $data
     * @return array
     */
    public function analyzeHomework(array $data): array
    {
        try {
            Log::info('Sending request to AI service', [
                'url' => $this->apiUrl . '/analyze-homework',
                'data' => $data
            ]);

            $response = $this->client->post('/analyze-homework', [
                'json' => $data,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error calling AI service: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'url' => $this->apiUrl . '/analyze-homework',
                'request_data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'content' => "I'm sorry, I'm having trouble processing your homework right now. Please try again later.",
                'explanation_level' => 1,
                'created_by_agent' => 'Error Handler',
                'additional_resources' => [],
            ];
        }
    }

    /**
     * Send message in ongoing real-time conversation.
     *
     * @param array $params
     * @return array
     */
    public function continueRealTimeFlow(array $data): string
    {
        try {
            Log::info('Calling AI real-time flow', [
                'url' => $this->apiUrl . '/real-time-conversation',
                'data' => $data
            ]);
    
            $response = $this->client->post('/real-time-conversation', [
                'json' => $data,
            ]);
    
            $result = json_decode($response->getBody()->getContents(), true);
    
            Log::info('AI real-time reply', ['result' => $result]);
    
            return $result['reply'] ?? 'No response.';
        } catch (GuzzleException $e) {
            Log::error('Real-time AI error: ' . $e->getMessage(), [
                'data' => $data
            ]);
            return 'Sorry, I am having trouble responding right now.';
        }
    }
    
    
    /**
     * Classify subject from homework content.
     *
     * @param string $content
     * @return string
     */
    public function classifySubject(string $content): string
    {
        try {
            $response = $this->client->post('/classify-subject', [
                'json' => [
                    'content' => $content,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['subject'] ?? 'mathematics';
        } catch (GuzzleException $e) {
            Log::error('Error calling AI service: ' . $e->getMessage());

            return 'mathematics';
        }
    }

    /**
     * Check if AI service is healthy.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        return Cache::remember('ai_service_health', 300, function () {
            try {
                $response = $this->client->get('/health');
                $result = json_decode($response->getBody()->getContents(), true);

                return $result['status'] === 'healthy';
            } catch (GuzzleException $e) {
                Log::error('AI service health check failed: ' . $e->getMessage());
                return false;
            }
        });
    }
}
