<div class="mt-16">
    <h2 class="text-2xl font-bold tracing-tight mb-4">Calculator</h2>

    <div class="p-6 rounded-md shadow-sm">
        <form wire:submit="calc">
            {{ $this->form }}

            <button type="submit" class="font-bold rounded-md">
                Calc
            </button>
        </form>
    </div>

    <x-filament-actions::modals />
</div>
