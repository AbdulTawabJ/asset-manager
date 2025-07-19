<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-start gap-2 mb-4">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-blue-600 text-gray-900 hover:text-white px-3 py-2 text-sm rounded">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Advanced Query
            </h2>
        </div>
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

        $columnTypes = [
            'serial_no' => 'string',
            'date_of_purchase' => 'date',
            'type' => 'string',
            'description' => 'text',
            'amount' => 'numeric',
            'location' => 'string',
            'owner_full_name' => 'string',
            'remarks' => 'text',
            'remarked_by' => 'string',
            'last_updated_on' => 'datetime',
        ];

        $operators = [
            'string' => ['=', '!=', 'LIKE', 'NOT LIKE'],
            'numeric' => ['=', '!=', '<', '<=', '>', '>='],
            'date' => ['=', '!=', '<', '<=', '>', '>='],
            'datetime' => ['=', '!=', '<', '<=', '>', '>='],
            'text' => ['LIKE', 'NOT LIKE'],
        ];
    @endphp

    <script>
    const operatorMap = @json($operators);
    const columnTypes = @json($columnTypes);

    document.addEventListener('DOMContentLoaded', () => {
        const columnSelect = document.getElementById('condition-column');
        const operatorSelect = document.getElementById('condition-operator');
        const valueInput = document.querySelector('input[name="condition_value"]');

        function updateOperators() {
            const selectedColumn = columnSelect.value;
            const type = columnTypes[selectedColumn];
            const operators = operatorMap[type] || [];

            operatorSelect.innerHTML = '';
            operators.forEach(op => {
                const opt = document.createElement('option');
                opt.value = op;
                opt.text = op;
                operatorSelect.appendChild(opt);
            });
        }

        function validateValueInput() {
            const selectedColumn = columnSelect.value;
            const type = columnTypes[selectedColumn];

            valueInput.removeAttribute('pattern');
            valueInput.removeAttribute('inputmode');
            valueInput.removeAttribute('type');

            if (type === 'numeric') {
                valueInput.setAttribute('type', 'number');
                valueInput.setAttribute('inputmode', 'decimal');
            } else if (type === 'date' || type === 'datetime') {
                valueInput.setAttribute('type', 'date');
            } else {
                valueInput.setAttribute('type', 'text');
            }
        }

        columnSelect.addEventListener('change', () => {
            updateOperators();
            validateValueInput();
        });

        // Initialize on page load
        updateOperators();
        validateValueInput();
    });
</script>

    <div class="py-12">
        <!-- Query Builder UI -->
        <form method="GET" action="{{ route('assets.query') }}" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
            <div class="bg-white shadow rounded-lg p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Fields -->
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Fields</h3>
                    @foreach ($columns as $key => $label)
                        <label class="block text-sm">
                            <input type="checkbox" name="fields[]" value="{{ $key }}" {{ in_array($key, request('fields', array_keys($columns))) ? 'checked' : '' }} class="mr-2">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <!-- Conditions -->
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Condition</h3>
                    <label class="block text-sm mb-1">Column:</label>
                    <select name="condition_column" class="w-full border rounded mb-2" id="condition-column">
                        <option value="">-- Select Column --</option>
                        @foreach ($columns as $key => $label)
                            <option value="{{ $key }}" {{ request('condition_column') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <label class="block text-sm mb-1">Operator:</label>
                    <select name="condition_operator" class="w-full border rounded mb-2" id="condition-operator">
                        <!-- Will be populated by JS -->
                    </select>

                    <label class="block text-sm mb-1">Value:</label>
                    <input type="text" name="condition_value" value="{{ request('condition_value') }}" class="w-full border rounded">
                </div>

                <!-- Preferences -->
                <div class='relative'>
                    <h3 class="font-semibold text-gray-700 mb-2">Preferences</h3>
                    <label class="block mb-2 text-sm">
                        Order By:
                        <select name="order_by" class="w-full mt-1 border rounded">
                            @foreach ($columns as $key => $label)
                                <option value="{{ $key }}" {{ request('order_by', 'serial_no') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="text-sm mt-2 ">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="order_dir" value="asc" {{ request('order_dir', 'asc') == 'asc' ? 'checked' : '' }} class="mr-2">
                            Ascending
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="order_dir" value="desc" {{ request('order_dir') == 'desc' ? 'checked' : '' }} class="mr-2">
                            Descending
                        </label>
                    </div>
                    <div class=" text-center w-100 absolute right-0 bottom-0">
                        <button type="submit" class="text-white bg-gray-400 hover:bg-yellow-500 px-6 py-2 rounded shadow">
                            <i class="fa-solid fa-play"></i> Run
                        </button>
                    </div>
                </div>
            </div>

        </form>

        <!-- Advanced Query Result Table -->
        <div class="max-w-7xl mx-auto mt-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <div class="p-4 border-b flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="text-lg font-semibold text-gray-800">
                            Assets - Custom Query Results
                        </div>
                    </div>
                </div>

                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr class="divide-x divide-gray-100">
                            @foreach ($columns as $key => $label)
                                @if (!request('fields') || in_array($key, request('fields')))
                                    <th class="px-4 py-2">{{ $label }}</th>
                                @endif
                            @endforeach
                            <th class="sticky right-0 bg-gray-600 px-4 py-2 text-right text-gray-100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr class="group hover:bg-gray-50 divide-x divide-gray-100">
                                @foreach ($columns as $key => $label)
                                    @if (!request('fields') || in_array($key, request('fields')))
                                        <td class="px-4 py-2">{{ $asset->$key ?? '' }}</td>
                                    @endif
                                @endforeach
                                <td class="sticky right-0 px-4 py-2 text-right bg-gray-700">
                                    <div class="invisible group-hover:visible flex justify-start space-x-2">
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="bg-gray-600 hover:bg-blue-600 text-blue-400 hover:text-white px-2 py-1 rounded">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Delete this asset?');">
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
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
