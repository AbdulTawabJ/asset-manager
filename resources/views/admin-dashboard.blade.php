<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    @php
        $columns = [
            'serial_no' => 'Serial No',
            'date_of_purchase' => 'Date of Purchase',
            'type' => 'Type',
            'description' => 'Description',
            'amount' => 'Amount',
            'location' => 'Location',
            'owner_full_name' => 'Owner',
            'remarks' => 'Remarks',
            'remarked_by' => 'Remarked By',
            'last_updated_on' => 'Updated On',
        ];

        $savedColumns = session('visible_columns', array_keys($columns));
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Welcome, {{ Auth::user()->full_name ?? Auth::user()->email }}!
                </div>
            </div>
        </div>

        <!-- Asset Display Table -->
        <div class="max-w-7xl mx-auto mt-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <div class="p-4 border-b flex justify-between items-center">
                    <div class="text-lg font-semibold text-gray-800">
                        Assets Overview
                    </div>
                    <div class="flex justify-center items-center space-x-2">
                        <div class="relative inline-block text-left">
                            <button onclick="toggleColumnDropdown()" class="bg-gray-100 hover:bg-gray-600 hover:text-white text-gray-700 px-4 py-2 rounded shadow text-sm">
                                <i class="fa-solid fa-grip-lines-vertical"></i>
                            </button>
                            <div id="columnDropdown" class="hidden z-10 mt-2 w-48 h-64 fixed overflow-x-auto bg-white border border-gray-200 rounded shadow right-0">
                                @foreach ($columns as $key => $label)
                                    <label class="block px-4 py-2 text-sm">
                                        <input type="checkbox" class="column-toggle mr-2" data-column="{{ $key }}" {{ in_array($key, $savedColumns) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('assets.create') }}" class="inline-block bg-green-700 hover:bg-green-600 text-white text-sm px-4 py-2 rounded shadow">
                            <i class="fa-solid fa-plus"></i> Asset
                        </a>
                    </div>
                </div>

                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr class="divide-x divide-gray-100">
                            @foreach ($columns as $key => $label)
                                <th class="px-4 py-2 column-{{ $key }}" style="display: {{ in_array($key, $savedColumns) ? 'table-cell' : 'none' }}">{{ $label }}</th>
                            @endforeach
                            <th class="sticky right-0 bg-gray-600 px-4 py-2 text-right text-gray-100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr class="group hover:bg-gray-50 divide-x divide-gray-100">
                                @foreach ($columns as $key => $label)
                                    <td class="px-4 py-2 column-{{ $key }}" style="display: {{ in_array($key, $savedColumns) ? 'table-cell' : 'none' }}">
                                        {{ $asset->$key ?? '' }}
                                    </td>
                                @endforeach
                                <td class="sticky right-0 px-4 py-2 text-right bg-gray-700">
                                    <div class="invisible group-hover:visible flex justify-start space-x-2">
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="bg-gray-600 hover:bg-blue-600 text-white px-2 py-1 rounded">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Delete this asset?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-gray-600 hover:bg-red-600 text-white px-2 py-1 rounded">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 flex justify-center w-full bg-gray-700 text-black">
                    {{ $assets->links() }}
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
                        body: JSON.stringify({ columns: selected })
                    });
                });
            });
        });
    </script>
</x-app-layout>
