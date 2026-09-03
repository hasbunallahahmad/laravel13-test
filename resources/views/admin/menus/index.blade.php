<x-layouts::app>
    <div>
        <h1>Menu Management</h1>

        <ul>
            @foreach ($menus as $menu)
                <li @if ($menu->parent_id !== null) data-parent-id="{{ $menu->parent_id }}" @endif>
                    {{ $menu->name }}
                    —
                    {{ $menu->label }}
                    —
                    {{ $menu->icon }}
                    —
                    {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                    —
                    {{ $menu->type }}
                    —
                    {{ $menu->target }}
                    —
                    {{ $menu->route_name ?? $menu->url }}
                    —
                    <span data-sort-order="{{ $menu->sort_order }}">
                        {{ $menu->sort_order }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts::app>
