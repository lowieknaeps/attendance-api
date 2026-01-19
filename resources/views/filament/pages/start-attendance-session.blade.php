<div>
    <x-filament::page wire:poll.2s="$refresh">
        @if($this->scannerStatus === 'not_paired')
            <div class="flex items-center gap-3 bg-red-50 border border-red-500 px-4 py-3 rounded-lg shadow-sm mb-4">
                <svg class="w-6 h-6 flex-shrink-0" style="color: #b91c1c" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold" style="color: #b91c1c">Scanner niet gekoppeld</span>
            </div>
        @elseif($this->scannerStatus === 'paired')
            <div class="flex items-center gap-3 bg-green-50 border border-green-500 px-4 py-3 rounded-lg shadow-sm mb-4">
                <svg class="w-6 h-6 flex-shrink-0" style="color: #15803d" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-semibold" style="color: #15803d">Scanner gekoppeld</span>
            </div>
        @endif

        @if ($this->session)
            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                    <h2 class="text-lg font-semibold">Actieve sessie</h2>

                    <p class="text-sm text-gray-500">
                        Vak: {{ $this->session->course?->name ?? 'Onbekend vak' }}
                    </p>

                    {{-- Tijd sinds start --}}
                    <p
                        class="text-xs text-gray-400 mt-1"
                        wire:poll.60s
                    >
                        {{ $this->sessionElapsed }}
                    </p>
                </div>
                </div>

                {{ $this->table }}
            </div>
        @else
            {{ $this->form }}
        @endif
    </x-filament::page>
</div>
