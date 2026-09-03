<x-layouts::app>
    <div>
        <h1>Create Menu</h1>

        <div>
            <label for="name">Name</label>
            <input id="name" type="text" name="name">
        </div>

        <div>
            <label for="label">Label</label>
            <input id="label" type="text" name="label">
        </div>

        <div>
            <label for="type">Type</label>

            <select id="type" name="type">
                <option value="route">Route</option>
                <option value="url">URL</option>
            </select>
        </div>

        <div>
            <label for="route_name">Route Name</label>
            <input id="route_name" type="text" name="route_name">
        </div>

        <div>
            <label for="url">URL</label>
            <input id="url" type="url" name="url">
        </div>

        <div>
            <label for="target">Target</label>

            <select id="target" name="target">
                <option value="_self">Same Window</option>
                <option value="_blank">New Window</option>
            </select>
        </div>

        <div>
            <label for="icon">Icon</label>
            <input id="icon" type="text" name="icon">
        </div>

        <div>
            <label for="sort_order">Sort Order</label>
            <input id="sort_order" type="number" name="sort_order" min="0">
        </div>

        <div>
            <label for="is_active">Status</label>

            <select id="is_active" name="is_active">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
            </select>
        </div>

        <div>
            <label for="parent_id">Parent Menu</label>

            <select id="parent_id" name="parent_id">
                <option value="">-- Root Menu --</option>

                @foreach ($parentMenus as $parentMenu)
                    <option value="{{ $parentMenu->id }}">
                        {{ $parentMenu->label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</x-layouts::app>
