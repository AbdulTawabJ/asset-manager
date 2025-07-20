<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-start gap-2">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-900 shadow px-3 py-2 text-sm rounded">
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

    function createConditionRow() {
        const row = document.createElement('div');
        row.className = 'condition-group mb-4 flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 items-start md:items-end';
        row.innerHTML = `
            <select name="condition_column[]" class="w-full md:w-1/3 border rounded condition-column focus:ring-yellow-500 focus:border-yellow-500">
                @foreach ($columns as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <select name="condition_operator[]" class="w-full md:w-1/4 border rounded condition-operator focus:ring-yellow-500 focus:border-yellow-500"></select>

            <input type="text" name="condition_value[]" class="w-full md:w-1/3 border rounded condition-value focus:ring-yellow-500 focus:border-yellow-500" placeholder="Value">

            <select name="condition_logic[]" class="w-full md:w-1/6 border rounded focus:ring-yellow-500 focus:border-yellow-500">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>

            <button type="button" class="remove-condition bg-transparent text-red-500 hover:bg-gray-200 px-1 bottom-2 rounded-full" ><i class="fa-solid fa-circle-xmark"></i></button>
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

    // ✅ Loop over all .condition-group elements and bind each
    container.querySelectorAll('.condition-group').forEach(row => {
        bindConditionEvents(row);
    });

    addBtn.addEventListener('click', () => {
        const row = createConditionRow();
        container.appendChild(row);
        bindConditionEvents(row);
    });
});

</script>

    <div class="py-12">
        <!-- Query Builder UI -->
        <form method="GET" action="{{ route('assets.query') }}" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
            <div class="bg-white shadow rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Fields -->
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Fields</h3>
                    @foreach ($columns as $key => $label)
                        <label class="block text-sm">
                            <input  type="checkbox" name="fields[]"  value="{{ $key }}" {{ in_array($key, request('fields', array_keys($columns))) ? 'checked' : '' }} class="text-yellow-500 mr-2 focus:ring-yellow-500 focus:border-yellow-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <!-- Conditions -->
<div class='relative'>
    <h3 class="font-semibold text-gray-700 mb-2 ">Conditions</h3>
    <div id="conditions-container" >
        @php
    $savedColumns   = request()->input('condition_column', []);
    $savedOperators = request()->input('condition_operator', []);
    $savedValues    = request()->input('condition_value', []);
    $savedLogics    = request()->input('condition_logic', []);
    $count = max(count($savedColumns), 1);
@endphp

@for ($i = 0; $i < $count; $i++)
    <div class="condition-group mb-4 flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 items-start md:items-end">
        <select name="condition_column[]" class="w-full md:w-1/3 border rounded condition-column focus:ring-yellow-500 focus:border-yellow-500">
            @foreach ($columns as $key => $label)
                <option value="{{ $key }}" {{ ($savedColumns[$i] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <select name="condition_operator[]" class="w-full md:w-1/4 border rounded condition-operator focus:ring-yellow-500 focus:border-yellow-500">
            {{-- options will be set dynamically in JS --}}
        </select>

        <input type="text" name="condition_value[]" value="{{ $savedValues[$i] ?? '' }}" class="w-full md:w-1/3 border rounded condition-value focus:ring-yellow-500 focus:border-yellow-500" placeholder="Value">

        <select name="condition_logic[]" class="w-full md:w-1/6 border rounded focus:ring-yellow-500 focus:border-yellow-500">
            <option value="AND" {{ ($savedLogics[$i] ?? '') === 'AND' ? 'selected' : '' }}>AND</option>
            <option value="OR" {{ ($savedLogics[$i] ?? '') === 'OR' ? 'selected' : '' }}>OR</option>
        </select>

        <button type="button" class="remove-condition bg-transparent text-red-500 hover:bg-gray-200 px-1 bottom-2 rounded-full"><i class="fa-solid fa-circle-xmark"></i></button>
    </div>
@endfor

    </div>
    <button type="button" id="add-condition" class="mt-2 bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-sm shadow hover:shadow-lg">
        <i class="fa-solid fa-plus"></i> Add Condition
    </button>
    <div class="text-center w-100 absolute bottom-0 right-0">
                <button type="submit" class="hover:bg-purple-600  text-gray-700 hover:text-white bg-gray-100 shadow hover:shadow-xl px-6 py-2 rounded shadow hover:shadow-lg">
                    <i class="fa-solid fa-play"></i> Run
                </button>
    </div>
    
</div>

<!-- Preferences -->
<div class='relative'>
    <h3 class="font-semibold text-gray-700 mb-2">Preferences</h3>
                    <label class="block mb-2 text-sm">
                        Order By:
                        <select name="order_by" class="w-full mt-1 border rounded focus:ring-yellow-500 focus:border-yellow-500">
                            @foreach ($columns as $key => $label)
                            <option value="{{ $key }}" {{ request('order_by', 'serial_no') == $key ? 'selected' : '' }}>
                                {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="text-sm mt-2 ">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="order_dir" value="asc" {{ request('order_dir', 'asc') == 'asc' ? 'checked' : '' }} class="mr-2 text-yellow-500 focus:ring-yellow-500 focus:border-yellow-500">
                            Ascending
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="order_dir" value="desc" {{ request('order_dir') == 'desc' ? 'checked' : '' }} class="mr-2 text-yellow-500 focus:ring-yellow-500 focus:border-yellow-500">
                            Descending
                        </label>
                    </div>
                </div>
            </div>
            
            
        </form>
        
        <!-- Advanced Query Result Table -->
        <div class="max-w-7xl mx-auto mt-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <div class="p-4 border-b flex flex-wrap justify-between items-center">
    <div class="text-lg font-semibold text-gray-800">
        Assets - Custom Query Results
    </div>

    <form method="GET" action="{{ route('assets.query.export') }}" id="export-form" class="mt-2 md:mt-0">
        @foreach(request()->all() as $key => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <button type="submit" class="hover:bg-cyan-600  text-gray-700 hover:text-white bg-gray-100 shadow hover:shadow-xl px-4 py-1.5 rounded shadow hover:shadow-lg text-sm">
            <i class="fa-solid fa-download mr-1"></i> Export CSV
        </button>
    </form>
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
