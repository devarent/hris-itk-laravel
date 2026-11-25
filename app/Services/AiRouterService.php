<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiRouterService
{
    /**
     * Klasifikasi kompleksitas prompt untuk Dynamic LLM Routing:
     * - light   -> Google Gemini 4 Hydrogen (cepat & hemat)
     * - medium  -> Google Gemini 4 Neon (analitik, SQL, coding)
     * - heavy   -> Google Gemini 4 Carbon (reasoning mendalam)
     * - glm     -> Z.ai GLM-5.4 / GLM-5.3 (Loop Transformer, 1M context, thinking mode)
     */
    public function selectTier(string $prompt, ?string $forceTier = null): string
    {
        if ($forceTier && in_array($forceTier, ['hydrogen', 'neon', 'carbon', 'glm'], true)) {
            return $forceTier;
        }

        $lower = Str::lower($prompt);
        $length = Str::length($prompt);

        if (Str::contains($lower, ['audit', 'keamanan', 'anomali', 'mendalam', 'forensik', 'kebijakan']) || $length > 800) {
            return 'carbon';
        }

        if (Str::contains($lower, ['sql', 'rekap', 'analisis', 'tren', 'perbandingan', 'kode', 'query']) || $length > 250) {
            return 'neon';
        }

        return 'hydrogen';
    }

    /**
     * Eksekusi permintaan ke model target dengan pencatatan metrik skripsi (latensi, token, model).
     */
    public function ask(string $prompt, string $contextSummary = '', ?string $forceTier = null): array
    {
        $tier = $this->selectTier($prompt, $forceTier);
        $startedAt = microtime(true);

        $result = match ($tier) {
            'glm' => $this->callZai($prompt, $contextSummary),
            default => $this->callGemini($tier, $prompt, $contextSummary),
        };

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        return array_merge($result, [
            'tier' => $tier,
            'latency_ms' => $durationMs,
        ]);
    }

    protected function callGemini(string $tier, string $prompt, string $contextSummary): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config("services.gemini.models.{$tier}", 'gemini-4-hydrogen');
        $baseUrl = rtrim((string) config('services.gemini.base_url'), '/');

        if (empty($apiKey)) {
            return $this->simulatedResponse($tier, $model, $prompt, $contextSummary, 'GEMINI_API_KEY belum dikonfigurasi di .env');
        }

        $fullPrompt = $contextSummary !== ''
            ? "Konteks Data Absensi HRIS ITK:\n{$contextSummary}\n\nPertanyaan:\n{$prompt}"
            : $prompt;

        $response = Http::timeout(120)->post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $fullPrompt]]],
            ],
        ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'provider' => 'Google Gemini 4',
                'model' => $model,
                'answer' => 'Gagal memanggil API Gemini: ' . $response->body(),
                'reasoning_content' => null,
                'usage' => [],
            ];
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return [
            'ok' => true,
            'provider' => 'Google Gemini 4',
            'model' => $model,
            'answer' => $text,
            'reasoning_content' => null,
            'usage' => $data['usageMetadata'] ?? [],
        ];
    }

    protected function callZai(string $prompt, string $contextSummary): array
    {
        $apiKey = config('services.zai.api_key');
        $model = config('services.zai.model', 'glm-5.3');
        $baseUrl = rtrim((string) config('services.zai.base_url'), '/');
        $timeout = (int) config('services.zai.timeout', 150);

        if (empty($apiKey)) {
            return $this->simulatedResponse('glm', $model, $prompt, $contextSummary, 'ZAI_API_KEY belum dikonfigurasi di .env');
        }

        $messages = [
            ['role' => 'system', 'content' => 'Anda adalah asisten analis HRIS & absensi berbasis Laravel. Jawab ringkas, akurat, dan terstruktur.'],
        ];

        if ($contextSummary !== '') {
            $messages[] = ['role' => 'system', 'content' => "Konteks Data Absensi:\n{$contextSummary}"];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'thinking' => ['type' => 'enabled'],
                'reasoning_effort' => 'high',
                'stream' => false,
            ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'provider' => 'Z.ai GLM',
                'model' => $model,
                'answer' => 'Gagal memanggil API Z.ai: ' . $response->body(),
                'reasoning_content' => null,
                'usage' => [],
            ];
        }

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? [];

        return [
            'ok' => true,
            'provider' => 'Z.ai GLM',
            'model' => $model,
            'answer' => $message['content'] ?? '',
            'reasoning_content' => $message['reasoning_content'] ?? null,
            'usage' => $data['usage'] ?? [],
        ];
    }

    protected function simulatedResponse(string $tier, string $model, string $prompt, string $contextSummary, string $reason): array
    {
        return [
            'ok' => true,
            'simulated' => true,
            'provider' => $tier === 'glm' ? 'Z.ai GLM (Simulasi Lokal)' : 'Google Gemini 4 (Simulasi Lokal)',
            'model' => $model,
            'answer' => "[Mode Simulasi — {$reason}]\n\nRouter memilih tier **{$tier}** (`{$model}`) untuk memproses: \"{$prompt}\".\n\nRingkasan konteks yang dikirim:\n{$contextSummary}",
            'reasoning_content' => $tier === 'glm' ? 'Simulasi reasoning_content: menganalisis ringkasan absensi dan menyusun rekomendasi.' : null,
            'usage' => [
                'prompt_tokens' => (int) ceil(Str::length($prompt . $contextSummary) / 4),
                'completion_tokens' => 64,
            ],
        ];
    }
}
