<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-start gap-2">
            <a href="{{ route('history.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-600 text-gray-900 hover:text-white shadow px-3 py-2 text-sm rounded">
                <i class="fa-solid fa-arrow-left"></i> Back to Asset Movement
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Custom Search
            </h2>
        </div>
    </x-slot>

    @php
        $columns = [
            'asset_tag' => 'Tag',
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

        $columnTypes = [
            'asset_tag' => 'string',
            'description' => 'text',
            'prev_location' => 'string',
            'new_location' => 'string',
            'prev_owner' => 'string',
            'new_owner' => 'string',
            'remarks' => 'text',
            'remarked_by' => 'string',
            'date' => 'date',
            'status' => 'string',

            'prev_region' => 'string',
            'new_region' => 'string',
            'prev_area' => 'string',
            'new_area' => 'string',
            'prev_branch' => 'string',
            'new_branch' => 'string',
            'prev_department' => 'string',
            'new_department' => 'string',
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

            function createConditionRow() {
        const row = document.createElement('div');
        row.className = 'condition-group mb-4 flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 items-start md:items-end';
        row.innerHTML = `
            <select name="condition_column[]" class="w-full md:w-1/3 border rounded condition-column">
                @foreach ($columns as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
                @foreach (['prev_region', 'prev_area', 'prev_branch', 'prev_department', 'new_region', 'new_area', 'new_branch', 'new_department'] as $locPart)
                    <option value="{{ $locPart }}">{{ ucwords(str_replace('_', ' ', $locPart)) }}</option>
                @endforeach
            </select>

            <select name="condition_operator[]" class="w-full md:w-1/4 border rounded condition-operator focus:ring-yellow-500 focus:border-yellow-500"></select>
            <input type="text" name="condition_value[]" class="w-full md:w-1/3 border rounded condition-value focus:ring-yellow-500 focus:border-yellow-500" placeholder="Value">
            <select name="condition_logic[]" class="w-full md:w-1/6 border rounded focus:ring-yellow-500 focus:border-yellow-500">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>
            <button type="button" class="remove-condition bg-transparent text-red-500 hover:bg-gray-200 px-1 bottom-2 rounded-full"><i class="fa-solid fa-circle-xmark"></i></button>
        `;
        return row;
    }

            function updateOperators(row) {
                const column = row.querySelector('.condition-column').value;
                const type = columnTypes[column];
                const ops = operatorMap[type] || [];

                const operatorSelect = row.querySelector('.condition-operator');
                operatorSelect.innerHTML = '';
                ops.forEach(op => {
                    const opt = document.createElement('option');
                    opt.value = op;
                    opt.text = op;
                    operatorSelect.appendChild(opt);
                });
            }

            function validateInputType(row) {
                const column = row.querySelector('.condition-column').value;
                const type = columnTypes[column];
                const input = row.querySelector('.condition-value');

                input.setAttribute('type', 'text');
                input.removeAttribute('inputmode');

                if (type === 'numeric') {
                    input.setAttribute('type', 'number');
                    input.setAttribute('inputmode', 'decimal');
                } else if (type === 'date' || type === 'datetime') {
                    input.setAttribute('type', 'date');
                }
            }

            function bindConditionEvents(row) {
                row.querySelector('.condition-column').addEventListener('change', () => {
                    updateOperators(row);
                    validateInputType(row);
                });

                row.querySelector('.remove-condition').addEventListener('click', () => {
                    row.remove();
                });

                updateOperators(row);
                validateInputType(row);
            }

            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('conditions-container');
                const addBtn = document.getElementById('add-condition');
                container.querySelectorAll('.condition-group').forEach(row => bindConditionEvents(row));

                addBtn.addEventListener('click', () => {
                    const row = createConditionRow();
                    container.appendChild(row);
                    bindConditionEvents(row);
                });
            });
        </script>
    

    <div class="py-12">
        <form method="GET" action="{{ route('history.query') }}" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
            <div class="bg-white shadow rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Columns</h3>
                    @foreach ($columns as $key => $label)
                        <label class="block text-sm">
                            <input type="checkbox" name="fields[]" value="{{ $key }}" {{ in_array($key, request('fields', array_keys($columns))) ? 'checked' : '' }} class="text-yellow-500 focus:ring-yellow-500 focus:border-yellow-500 mr-2">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div class='relative'>
                    <h3 class="font-semibold text-gray-700 mb-2">Searching Conditions</h3>
                    <div id="conditions-container">
                        @php
                            $savedColumns = request()->input('condition_column', []);
                            $savedOperators = request()->input('condition_operator', []);
                            $savedValues = request()->input('condition_value', []);
                            $savedLogics = request()->input('condition_logic', []);
                            $count = count($savedColumns);
                        @endphp
                        @for ($i = 0; $i < $count; $i++)
                            <div class="condition-group mb-4 flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 items-start md:items-end">
                                <select name="condition_column[]" class="w-full md:w-1/3 border rounded condition-column">
                                    @foreach ($columns as $key => $label)
                                        <option value="{{ $key }}" {{ ($savedColumns[$i] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                    @foreach (['prev_region', 'prev_area', 'prev_branch', 'prev_department', 'new_region', 'new_area', 'new_branch', 'new_department'] as $locPart)
                                        <option value="{{ $locPart }}" {{ ($savedColumns[$i] ?? '') === $locPart ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $locPart)) }}</option>
                                    @endforeach
                                </select>

                                <select name="condition_operator[]" class="w-full md:w-1/4 border rounded condition-operator"></select>
                                <input type="text" name="condition_value[]" value="{{ $savedValues[$i] ?? '' }}" class="w-full md:w-1/3 border rounded condition-value" placeholder="Value">
                                <select name="condition_logic[]" class="w-full md:w-1/6 border rounded">
                                    <option value="AND" {{ ($savedLogics[$i] ?? '') === 'AND' ? 'selected' : '' }}>AND</option>
                                    <option value="OR" {{ ($savedLogics[$i] ?? '') === 'OR' ? 'selected' : '' }}>OR</option>
                                </select>
                                <button type="button" class="remove-condition text-red-500 hover:bg-gray-200 px-1 rounded-full"><i class="fa-solid fa-circle-xmark"></i></button>
                            </div>
                        @endfor
                    </div>
                    <button type="button" id="add-condition" class="mt-2 bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-sm shadow hover:shadow-lg">
                        <i class="fa-solid fa-plus"></i> Add Condition
                    </button>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Preferences</h3>
                    <label class="block text-sm">
                        Sort On:
                        <select name="order_by" class="w-full mt-1 border rounded focus:ring-yellow-500 focus:border-yellow-500">
                            @foreach ($columns as $key => $label)
                                <option value="{{ $key }}" {{ request('order_by', 'date') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="mt-2 text-sm">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="order_dir" value="asc" {{ request('order_dir', 'desc') == 'asc' ? 'checked' : '' }} class="text-yellow-500 focus:ring-yellow-500 focus:border-yellow-500 mr-2">
                            Ascending
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="order_dir" value="desc" {{ request('order_dir', 'desc') == 'desc' ? 'checked' : '' }} class="text-yellow-500 focus:ring-yellow-500 focus:border-yellow-500 mr-2">
                            Descending
                        </label>
                    </div>
                </div>
                <div class="flex text-center w-100 justify-end items-end">
                        <button type="submit" class="hover:bg-purple-600 transition ease-in text-gray-700 hover:text-white bg-gray-100 shadow hover:shadow-xl px-6 py-2 rounded shadow hover:shadow-lg">
                            <i class="fa-solid fa-play"></i> Run
                        </button>
                </div>
            </div>
        </form>

        @if(isset($history) && count($history))
        <div class="max-w-7xl mx-auto mt-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <div class="p-4 border-b flex justify-between items-center">
                    <div class="text-lg font-semibold text-gray-800">Asset Shifts - Custom Query Results</div>
                    <form method="GET" action="{{ route('history.query.export') }}">
                        @foreach(request()->all() as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <button type="submit" class="hover:bg-cyan-600 transition ease-in text-gray-700 hover:text-white bg-gray-100 shadow  hover:shadow-lg px-4 py-2 rounded text-sm">
                            <i class="fa-solid fa-download mr-1"></i> Export CSV
                        </button>
                    </form>
                </div>

                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            @foreach ($columns as $key => $label)
                                @if (!request('fields') || in_array($key, request('fields')))
                                    <th class="px-4 py-2">{{ $label }}</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $entry)
                            <tr class="hover:bg-gray-50 divide-x divide-gray-100">
                                @foreach ($columns as $key => $label)
                                    @if (!request('fields') || in_array($key, request('fields')))
                                        <td class="px-4 py-2">{{ $entry->$key ?? '' }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
