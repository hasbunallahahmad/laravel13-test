<x-layouts::admin :title="__('Edit Content')">
    <div>
        <flux:heading size="xl">
            {{ __('Edit Content') }}
        </flux:heading>

        <flux:text class="mt-2">
            {{ $content->title }}
        </flux:text>
    </div>
</x-layouts::admin>
