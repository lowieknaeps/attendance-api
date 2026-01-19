<x-filament::page>
    <div class="space-y-4">

        <div class="flex items-center gap-3">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="number"
                    min="10"
                    max="2000"
                    wire:model.defer="lines"
                />
            </x-filament::input.wrapper>

            <x-filament::button wire:click="refreshLog">
                Refresh
            </x-filament::button>

            <span class="text-sm text-gray-500">
                Auto refresh every 5s
            </span>
        </div>

        <x-filament::card>
            <pre class="text-xs whitespace-pre-wrap leading-relaxed"
                 wire:poll.5s="refreshLog">{{ $logText }}</pre>
        </x-filament::card>
        
        <x-filament::button color="danger" wire:click="clearLog"
            wire:confirm="Ben je zeker dat je de scan logs wilt wissen?">
            Clear log
        </x-filament::button>

    </div>
</x-filament::page>