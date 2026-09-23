<x-layouts::admin :title="__('Create Content')">
    <div class="space-y-6">

        {{-- Page Header --}}
        <div>
            <flux:heading size="xl">
                {{ __('Create Content') }}
            </flux:heading>

            <flux:text class="mt-2">
                {{ __('Create and publish website content.') }}
            </flux:text>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <flux:callout variant="danger" icon="exclamation-triangle">
                <flux:heading size="sm">
                    {{ __('Please correct the errors below.') }}
                </flux:heading>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif

        {{-- Content Form --}}
        <form method="POST" action="{{ route('admin.contents.store') }}">
            @csrf

            <div class="space-y-6">

                {{-- Basic Information --}}
                <div class="rounded-xl border border-app-border bg-app-surface p-6">
                    <div class="mb-6">
                        <flux:heading size="lg">
                            {{ __('Basic Information') }}
                        </flux:heading>

                        <flux:text class="mt-1">
                            {{ __('Define the main information for this content.') }}
                        </flux:text>
                    </div>

                    <div class="grid gap-6">

                        {{-- Type --}}
                        <flux:field>
                            <flux:label for="type">
                                {{ __('Type') }}
                            </flux:label>

                            <flux:select name="type" id="type" required>
                                <option value="">
                                    {{ __('Select type') }}
                                </option>

                                @foreach (\App\Enums\ContentType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('type') === $type->value)>
                                        {{ ucfirst($type->value) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:error name="type" />
                        </flux:field>

                        {{-- Title --}}
                        <flux:field>
                            <flux:label for="title">
                                {{ __('Title') }}
                            </flux:label>

                            <flux:input name="title" id="title" value="{{ old('title') }}" required
                                minlength="3" maxlength="255" data-slug-source />

                            <flux:error name="title" />
                        </flux:field>

                        {{-- Slug --}}
                        <flux:field>
                            <flux:label for="slug">
                                {{ __('Slug') }}
                            </flux:label>

                            <flux:input name="slug" id="slug" value="{{ old('slug') }}" required
                                minlength="3" maxlength="255" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" data-slug-target />

                            <flux:text class="mt-1">
                                {{ __('Use lowercase letters, numbers, and hyphens only.') }}
                            </flux:text>

                            <flux:error name="slug" />
                        </flux:field>

                        {{-- Excerpt --}}
                        <flux:field>
                            <flux:label for="excerpt">
                                {{ __('Excerpt') }}
                            </flux:label>

                            <flux:textarea name="excerpt" id="excerpt" rows="4" maxlength="1000">
                                {{ old('excerpt') }}</flux:textarea>

                            <flux:error name="excerpt" />
                        </flux:field>

                        {{-- Body --}}
                        @include('admin.contents.partials.rich-text-editor', [
                            'value' => old('body'),
                        ])

                    </div>
                </div>

                {{-- Publishing --}}
                <div class="rounded-xl border border-app-border bg-app-surface p-6">
                    <div class="mb-6">
                        <flux:heading size="lg">
                            {{ __('Publishing') }}
                        </flux:heading>

                        <flux:text class="mt-1">
                            {{ __('Set the content status, author, and publication date.') }}
                        </flux:text>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">

                        {{-- Status --}}
                        <flux:field>
                            <flux:label for="status">
                                {{ __('Status') }}
                            </flux:label>

                            <flux:select name="status" id="status" required>
                                <option value="">
                                    {{ __('Select status') }}
                                </option>

                                @foreach (\App\Enums\ContentStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', \App\Enums\ContentStatus::DRAFT->value) === $status->value)>
                                        {{ ucfirst($status->value) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:error name="status" />
                        </flux:field>

                        {{-- Author --}}
                        <flux:field>
                            <flux:label for="author_uuid">
                                {{ __('Author') }}
                            </flux:label>

                            <flux:select name="author_uuid" id="author_uuid">
                                <option value="">
                                    {{ __('No author') }}
                                </option>

                                @foreach ($authors as $author)
                                    <option value="{{ $author->uuid }}" @selected(old('author_uuid') === $author->uuid)>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:error name="author_uuid" />
                        </flux:field>

                        {{-- Published At --}}
                        <flux:field class="sm:col-span-2">
                            <flux:label for="published_at">
                                {{ __('Published At') }}
                            </flux:label>

                            <flux:input type="datetime-local" name="published_at" id="published_at"
                                value="{{ old('published_at') }}" />

                            <flux:error name="published_at" />
                        </flux:field>

                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap justify-end gap-2">
                    <flux:button :href="route('admin.contents.index')" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary" icon="check">
                        {{ __('Create Content') }}
                    </flux:button>
                </div>

            </div>
        </form>
    </div>
    @include('admin.contents.partials.rich-text-editor-script')
    @include('admin.contents.partials.slug-script')
</x-layouts::admin>
