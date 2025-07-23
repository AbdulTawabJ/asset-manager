{{-- resources/views/admin-history.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asset History
        </h2>
    </x-slot>

    @php
        $columns = [
            'asset_tag' => 'Asset Tag',
            'description' => 'Description',
            'prev_location' => 'Previous Location',
            'new_location' => 'New Location',
            'prev_owner' => 'Previous Owner',
            'new_owner' => 'New Owner',
            'remarks' => 'Remarks',
            'remarked_by' => 'Remarked By',
            'date' => 'Date',
            'status' => 'Status',
        ];
        $savedColumns = session('visible_columns_asset_history', array_keys($columns));
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex justify-between items-center flex-wrap gap-2">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-gem pr-1"></i> Assets
                        </a>
                        <div class = "transition ease-in cursor-not-allowed bg-gray-800 text-white px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-hand-holding-hand pr-1"></i> Shift Log
                        </div>
                        <a href="{{ route('employees.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-user pr-1"></i> Employees
                        </a>
                        <a href="{{ route('departments.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-house pr-1"></i> Departments
                        </a>
                        <a href="{{ route('locations.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-location-dot pr-1"></i> Locations
                        </a>
                        <a href="{{ route('types.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-layer-group pr-1"></i> Types
                        </a>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('history.query') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm shadow-lg hover:shadow-xl transition ease-in">
                            <i class="fa-solid fa-filter"></i> Advanced Query
                        </a>
                        <form method="GET" action="{{ route('history.export') }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <button type="submit" class="transition ease-intransition ease-in  hover:bg-cyan-600 text-gray-700 hover:text-white bg-gray-100 shadow px-4 py-2 rounded text-sm hover:shadow-xl">
                                <i class="fa-solid fa-download"></i> Export CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <div class="p-4 border-b flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="text-lg font-semibold text-gray-800">Shift Records</div>
                        <form action="{{ route('history.index') }}" method="GET" class="flex items-center ml-4">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border-white rounded-left px-2 py-1 text-sm shadow-lg focus:ring-purple-600">
                            <button type="submit" class="text-purple-700 bg-gray-100 hover:text-white hover:bg-purple-600 px-3 py-1 rounded-right shadow-lg">
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-center items-center space-x-2">
                        <div class="relative inline-block text-left">
                            <button onclick="toggleColumnDropdown()" class="bg-gray-100 hover:bg-yellow-400 hover:text-white text-yellow-500 px-4 py-2 rounded shadow text-sm hover:shadow-lg">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                            <div id="columnDropdown" class="hidden z-10 mt-2 w-64 h-64 absolute overflow-x-auto bg-white border border-gray-200 rounded shadow right-0">
                                @foreach ($columns as $key => $label)
                                    <label class="block px-4 py-2 text-sm">
                                        <input type="checkbox" class="column-toggle mr-2 text-yellow-400" data-column="{{ $key }}" {{ in_array($key, $savedColumns) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr class="divide-x divide-gray-100">
                            @foreach ($columns as $key => $label)
                                <th class="px-4 py-2 column-{{ $key }}" style="display: {{ in_array($key, $savedColumns) ? 'table-cell' : 'none' }}">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $entry)
                            <tr class="group hover:bg-gray-50 divide-x divide-gray-100">
                                @foreach ($columns as $key => $label)
                                    <td class="px-4 py-2 column-{{ $key }}" style="display: {{ in_array($key, $savedColumns) ? 'table-cell' : 'none' }}">
                                        {{ $entry->$key ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 flex justify-center w-full bg-gray-700 text-black">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleColumnDropdown() {
            document.getElementById('columnDropdown').classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('.column-toggle');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const columnClass = 'column-' + this.dataset.column;
                    const cells = document.querySelectorAll('.' + columnClass);
                    cells.forEach(cell => {
                        cell.style.display = this.checked ? '' : 'none';
                    });

                    const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.dataset.column);
                    fetch("{{ route('settings.save-columns') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ columns: selected, table: 'asset_history' })
                    });
                });
            });
        });
    </script>
</x-app-layout>
