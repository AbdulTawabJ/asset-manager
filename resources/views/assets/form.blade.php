<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($asset) ? 'Edit Asset' : 'Add New Asset' }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 rounded">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($asset) ? route('assets.update', $asset->id) : route('assets.store') }}">
            @csrf
            @if(isset($asset))
                @method('PUT')
            @endif

            <!-- Serial No -->
            <x-input-label for="serial_no" value="Serial Number" />
            <x-text-input name="serial_no" class="block w-full" value="{{ old('serial_no', $asset->serial_no ?? '') }}" />
            @error('serial_no')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Date of Purchase -->
            <x-input-label for="date_of_purchase" value="Date of Purchase" class="mt-4" />
            <x-text-input type="date" name="date_of_purchase" class="block w-full" value="{{ old('date_of_purchase', $asset->date_of_purchase ?? '') }}" />
            @error('date_of_purchase')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Asset Type -->
            <x-input-label for="type" value="Asset Type" class="mt-4" />
            <select name="type" class="block w-full border rounded">
                @foreach ($types as $type)
                    <option value="{{ $type->type }}" @selected(old('type', $asset->type ?? '') == $type->type)>
                        {{ $type->type }}
                    </option>
                @endforeach
            </select>
            @error('type')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Description -->
            <x-input-label for="description" value="Description" class="mt-4" />
            <textarea name="description" class="block w-full">{{ old('description', $asset->description ?? '') }}</textarea>
            @error('description')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Amount -->
            <x-input-label for="amount" value="Amount" class="mt-4" />
            <x-text-input type="number" name="amount" class="block w-full" value="{{ old('amount', $asset->amount ?? '') }}" />
            @error('amount')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Location -->
            <x-input-label for="location" value="Location" class="mt-4" />
            <select name="location" class="block w-full border rounded">
                @foreach ($locations as $location)
                    <option value="{{ $location->location }}" @selected(old('location', $asset->location ?? '') == $location->location)>
                        {{ $location->location }}
                    </option>
                @endforeach
            </select>
            @error('location')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Owner -->
            <x-input-label for="owner" value="Owner" class="mt-4" />
            <select name="owner" class="block w-full border rounded">
                @foreach ($employees as $emp)
                    <option value="{{ $emp->file_no }}" @selected(old('owner', $asset->owner ?? '') == $emp->file_no)>
                        {{ $emp->file_no }} - {{ $emp->first_name }} {{ $emp->last_name }}
                    </option>
                @endforeach
            </select>
            @error('owner')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Remarks -->
            <x-input-label for="remarks" value="Remarks" class="mt-4" />
            <textarea name="remarks" class="block w-full">{{ old('remarks', $asset->remarks ?? '') }}</textarea>
            @error('remarks')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Requires IT Remark -->
            <label class="flex items-center mt-4">
                <input type="checkbox" name="requires_it_remark" class="mr-2"
                       {{ old('requires_it_remark', $asset->requires_it_remark ?? false) ? 'checked' : '' }}>
                Requires IT Remark
            </label>
            @error('requires_it_remark')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Submit -->
            <div class="mt-4 flex space-x-2">
    <x-primary-button class='bg-green-800 hover:bg-green-600'>
        {{ isset($asset) ? 'Update Asset' : 'Add Asset' }}
    </x-primary-button>

    <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
        Cancel
    </a>
</div>

        </form>
    </div>
</x-app-layout>
