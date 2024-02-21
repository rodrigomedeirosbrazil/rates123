<div>
    <form wire:submit="calc" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            {{ __('Calculate') }}
        </x-filament::button>
    </form>

    <div class="min-w-full align-middle m-4">
        <table class="min-w-full border divide-y divide-gray-200">
            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                @forelse($receiptItems as $receiptItem)
                    <tr class="bg-white" wire:key="{{ $receiptItem?->name }}">
                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                            {{ __($receiptItem?->name) }}
                        </td>
                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                            {{ $receiptItem?->amount }}
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="2" class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                            No items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
