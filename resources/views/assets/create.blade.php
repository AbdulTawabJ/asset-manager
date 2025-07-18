<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New Asset</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        <form method="POST" action="{{ route('assets.store') }}" class="space-y-6">

            @csrf

            <x-input-label for="serial_no" :value="'Serial Number'" />
            <x-text-input id="serial_no" name="serial_no" type="text" required class="block w-full" />

            <x-input-label for="date_of_purchase" :value="'Date of Purchase'" />
            <x-text-input id="date_of_purchase" name="date_of_purchase" type="date" class="block w-full" />

            <x-input-label for="type" :value="'Asset Type'" />
            <select id="type" name="type" class="block w-full rounded border-gray-300">
                @foreach($types as $type)
                    <option value="{{ $type->type }}">{{ $type->type }}</option>
                @endforeach
            </select>

            <x-input-label for="description" :value="'Description'" />
            <textarea id="description" name="description" class="block w-full border-gray-300 rounded"></textarea>

            <x-input-label for="amount" :value="'Amount'" />
            <x-text-input id="amount" name="amount" type="number" step="0.01" class="block w-full" />

            <x-input-label for="location" :value="'Location'" />
            <select id="location" name="location" class="block w-full rounded border-gray-300">
                @foreach($locations as $location)
                    <option value="{{ $location->location }}">{{ $location->location }}</option>
                @endforeach
            </select>

            <x-input-label for="owner" :value="'Assigned Employee'" />
            <select id="owner" name="owner" class="block w-full rounded border-gray-300">
                @foreach($employees as $emp)
                    <option value="{{ $emp->file_no }}">
                        {{ $emp->file_no }} - {{ $emp->first_name }} {{ $emp->middle_name }} {{ $emp->last_name }}
                    </option>
                @endforeach
            </select>

            <x-input-label for="remarks" :value="'Remarks'" />
            <textarea id="remarks" name="remarks" class="block w-full border-gray-300 rounded"></textarea>

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="requires_it_remark" class="rounded border-gray-300">
                <span>Requires IT Remark</span>
            </label>

            <x-primary-button>Add Asset</x-primary-button>
        </form>
        
    </div>
</x-app-layout>
