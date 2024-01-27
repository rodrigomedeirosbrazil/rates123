<div>
    <form wire:submit="calc" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Calc
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>
