@extends('layouts.app')
@section('title', 'AI Asisten & Benchmark Absensi')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">AI Asisten & Dynamic LLM Router</h2>
        <p class="text-xs text-gray-500">Eksperimen routing otomatis & perbandingan model (Gemini 4 Hydrogen / Neon / Carbon & Z.ai GLM-5.4).</p>
    </div>
    <x-button variant="secondary" href="{{ route('absensi.index') }}">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Absensi</span>
    </x-button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Prompt -->
    <div class="lg:col-span-2 card p-6 bg-white border border-gray-200">
        <form method="POST" action="{{ route('absensi.ai-assistant') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Mode Pemilihan Model (Router Tier)</label>
                <select name="tier" class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="auto" {{ $tier === 'auto' ? 'selected' : '' }}>Auto Dynamic Router (Hydrogen / Neon / Carbon)</option>
                    <option value="hydrogen" {{ $tier === 'hydrogen' ? 'selected' : '' }}>Google Gemini 4 Hydrogen (Cepat & Hemat)</option>
                    <option value="neon" {{ $tier === 'neon' ? 'selected' : '' }}>Google Gemini 4 Neon (Analitik & Coding)</option>
                    <option value="carbon" {{ $tier === 'carbon' ? 'selected' : '' }}>Google Gemini 4 Carbon (Deep Reasoning / Audit)</option>
                    <option value="glm" {{ $tier === 'glm' ? 'selected' : '' }}>Z.ai GLM-5.4 / GLM-5.3 (Loop Transformer + Thinking)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Prompt / Pertanyaan Analisis Absensi</label>
                <textarea name="prompt" rows="4" required
                          placeholder="Contoh: Analisis ringkasan keterlambatan hari ini dan buatkan rekomendasi kebijakan absensi..."
                          class="w-full p-3 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('prompt', $prompt) }}</textarea>
                @error('prompt')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-sm transition-all">
                    <i class="bi bi-stars"></i>
                    <span>Jalankan Analisis AI</span>
                </button>
            </div>
        </form>

        @if($result)
        <div class="mt-6 pt-6 border-t border-gray-200 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <x-badge variant="default">{{ $result['provider'] ?? 'LLM' }}</x-badge>
                    <x-badge variant="secondary">{{ $result['model'] ?? '-' }}</x-badge>
                    <x-badge variant="outline">Tier: {{ strtoupper($result['tier'] ?? '-') }}</x-badge>
                </div>
                <span class="text-xs font-mono text-gray-500">Latensi: {{ $result['latency_ms'] ?? 0 }} ms</span>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 whitespace-pre-wrap leading-relaxed">{{ $result['answer'] ?? '' }}</div>

            @if(!empty($result['reasoning_content']))
            <details class="rounded-lg border border-amber-200 bg-amber-50/50 p-3">
                <summary class="text-xs font-semibold text-amber-800 cursor-pointer">Lihat Reasoning Content (Thinking Mode)</summary>
                <div class="mt-2 text-xs text-amber-900 whitespace-pre-wrap font-mono">{{ $result['reasoning_content'] }}</div>
            </details>
            @endif
        </div>
        @endif
    </div>

    <!-- Panel Informasi Metrik Skripsi -->
    <div class="card p-6 bg-white border border-gray-200 space-y-4">
        <h3 class="text-sm font-bold text-gray-900">Matriks Routing Skripsi</h3>
        <div class="space-y-3 text-xs text-gray-600">
            <div class="p-3 rounded-lg bg-emerald-50/60 border border-emerald-200">
                <div class="font-bold text-emerald-800">Hydrogen (156 tok/s)</div>
                <p class="mt-0.5">Query singkat & rekap harian ringan. Biaya paling rendah & tersedia free tier.</p>
            </div>
            <div class="p-3 rounded-lg bg-indigo-50/60 border border-indigo-200">
                <div class="font-bold text-indigo-800">Neon (101 tok/s)</div>
                <p class="mt-0.5">Tugas analitik, SQL, tren kehadiran, dan generasi kode Laravel.</p>
            </div>
            <div class="p-3 rounded-lg bg-slate-100 border border-slate-300">
                <div class="font-bold text-slate-800">Carbon (59 tok/s)</div>
                <p class="mt-0.5">Audit keamanan kode, deteksi anomali kompleks, dan reasoning mendalam.</p>
            </div>
            <div class="p-3 rounded-lg bg-violet-50/60 border border-violet-200">
                <div class="font-bold text-violet-800">GLM-5.4 (Loop Transformer)</div>
                <p class="mt-0.5">Konteks 1M token, 128K output, serta <code>reasoning_content</code> persisten.</p>
            </div>
        </div>
    </div>
</div>
@endsection
