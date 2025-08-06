<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Employees
        </h2>
    </x-slot>

    @php
        $columns = [
            'file_no' => 'File No',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'department' => 'Department',
        ];
        $savedColumns = session('visible_columns_employees', array_keys($columns));
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex justify-between items-center flex-wrap gap-2">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-gem pr-1"></i>Assets</a>
                        <a href="{{ route('history.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 px-4 py-2 rounded text-sm"><i class="fa-solid fa-hand-holding-hand pr-1"></i> Asset Movement</a>
                        <div class = "transition ease-in cursor-not-allowed bg-gray-800 text-white px-4 py-2 rounded text-sm"><i class="fa-solid fa-user pr-1"></i> Employees</div>
                        <a href="{{ route('departments.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 px-4 py-2 rounded text-sm"><i class="fa-solid fa-house pr-1"></i> Departments</a>
                        <a href="{{ route('locations.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 px-4 py-2 rounded text-sm"><i class="fa-solid fa-location-dot pr-1"></i> Locations</a>
                        <a href="{{ route('types.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 px-4 py-2 rounded text-sm"><i class="fa-solid fa-layer-group pr-1"></i> Types</a>
                    </div>

                    <div class="flex gap-2">
                        
                        <div  class = "cursor-not-allowed bg-purple-400 text-gray-200 disabled px-4 py-2 rounded text-sm ">
                            <i class="fa-regular fa-file"></i> Report
                        </div>
                        <form method="GET" action="{{ route('employees.export') }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <button type="submit" class="transition ease-in hover:bg-cyan-600 text-gray-700 hover:text-white bg-gray-100 shadow px-4 py-2 rounded text-sm hover:shadow-xl">
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
                        <div class="text-lg font-semibold text-gray-800">Employee Records</div>
                        <form action="{{ route('employees.index') }}" method="GET" class="flex items-center ml-4">
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
                            <div id="columnDropdown" class="hidden z-10 mt-2 w-48 h-48 absolute overflow-x-auto bg-white border border-gray-200 rounded shadow right-0">
                                @foreach ($columns as $key => $label)
                                    <label class="block px-4 py-2 text-sm">
                                        <input type="checkbox" class="column-toggle mr-2 text-yellow-400" data-column="{{ $key }}" {{ in_array($key, $savedColumns) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('employees.create') }}" class="transition ease-in bg-green-700 hover:bg-green-600 text-white text-sm px-4 py-2 rounded hover:shadow-lg">
                            <i class="fa-solid fa-plus"></i> Add Employee
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
                        @foreach ($employees as $employee)
                            <tr class="group hover:bg-gray-50 divide-x divide-gray-100">
                                @foreach ($columns as $key => $label)
                                    <td class="px-4 py-2 column-{{ $key }}" style="display: {{ in_array($key, $savedColumns) ? 'table-cell' : 'none' }}">
                                        {{ $employee->$key ?? '' }}
                                    </td>
                                @endforeach
                                <td class="sticky right-0 px-4 py-2 text-right bg-gray-700">
                                    <div class="invisible group-hover:visible flex justify-start space-x-2">
                                        <a href="{{ route('employees.edit', $employee->file_no) }}" class="bg-gray-600 hover:bg-blue-600 text-blue-400 hover:text-white px-2 py-1 rounded">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee->file_no) }}" method="POST" onsubmit="return confirm('Delete this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-gray-600 hover:bg-red-600 text-red-400 hover:text-white px-2 py-1 rounded">
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
                    {{ $employees->links() }}
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
                        body: JSON.stringify({ columns: selected, table: 'employees' })
                    });
                });
            });
        });
    </script>
</x-app-layout>
