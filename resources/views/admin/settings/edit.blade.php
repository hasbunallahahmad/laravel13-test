<x-layouts::admin :title="__('Edit Setting')">
    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <flux:heading size="xl">
                {{ __('Edit Setting') }}
            </flux:heading>

            <flux:text class="mt-2">
                {{ __('Update the selected website setting.') }}
            </flux:text>
        </div>

        {{-- Form --}}
        <form method="POST"
            action="{{ route('admin.settings.update', [
                'group' => $setting->group,
                'key' => $setting->key,
            ]) }}"
            class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">

                {{-- Setting Name --}}
                <div class="mb-6">
                    <flux:label>
                        {{ __('Setting') }}
                    </flux:label>

                    <div class="mt-2 font-medium text-zinc-900 dark:text-white">
                        {{ str($setting->key)->replace('_', ' ')->title() }}
                    </div>
                </div>

                {{-- Value --}}
                <div>
                    <flux:label for="value">
                        {{ __('Value') }}
                    </flux:label>

                    @if ($setting->type === 'boolean')
                        {{-- Ensure false is submitted when checkbox is unchecked --}}
                        <input type="hidden" name="value" value="0">

                        <input id="value" name="value" type="checkbox" value="1" @checked((bool) old('value', $setting->value))
                            class="mt-2">
                    @elseif ($setting->type === 'integer')
                        <flux:input id="value" name="value" type="number"
                            value="{{ old('value', $setting->value) }}" class="mt-2" />
                    @elseif ($setting->type === 'json')
                        <textarea id="value" name="value" rows="12"
                            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white p-3 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-900">{{ old(
                                'value',
                                json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            ) }}</textarea>
                    @else
                        <flux:input id="value" name="value" type="text"
                            value="{{ old('value', $setting->value) }}" class="mt-2" />
                    @endif

                    @error('value')
                        <flux:text class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </flux:text>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex items-center gap-3">

                    <flux:button type="submit" variant="primary">
                        {{ __('Save Changes') }}
                    </flux:button>

                    <flux:button href="{{ route('admin.settings.index') }}" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>

                </div>

            </div>
        </form>

    </div>
</x-layouts::admin>
