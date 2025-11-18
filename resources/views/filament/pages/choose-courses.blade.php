<x-filament::page>
    {{ $this->form }}
    <x-filament::button class="mt-4" wire:click="save">
        Opslaan
    </x-filament::button>
</x-filament::page>