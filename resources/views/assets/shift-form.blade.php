<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Shift Asset: {{ $asset->asset_tag }}
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

        <form method="POST" action="{{ route('asset_history.store') }}">
            @csrf

            <!-- Hidden Serial No -->
            <input type="hidden" name="asset_tag" value="{{ $asset->asset_tag }}">

            <!-- Description -->
            <x-input-label for="description" value="Description of Shift" />
            <textarea name="description" class="block w-full">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Previous Location -->
            <x-input-label for="prev_location" value="Previous Location" class="mt-4" />
            <x-text-input name="prev_location" class=" cursor-not-allowed block w-full" value="{{ $asset->location }}" readonly />

            <!-- New Location -->
            <x-input-label for="new_location" value="New Location" class="mt-4" />
            <select name="new_location" class="block w-full border rounded">
                @foreach ($locations as $loc)
                    <option value="{{ $loc->location }}" @selected(old('new_location') == $loc->location)>
                        {{ $loc->location }}
                    </option>
                @endforeach
            </select>
            @error('new_location')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Previous Owner -->
            <x-input-label for="prev_owner" value="Previous Owner" class="mt-4" />
            <x-text-input name="prev_owner" class=" cursor-not-allowed block w-full" value="{{ $asset->owner }}" readonly />

            <!-- New Owner -->
            <x-input-label for="new_owner" value="New Owner" class="mt-4" />
            <select name="new_owner" class="block w-full border rounded">
                @foreach ($employees as $emp)
                    <option value="{{ $emp->file_no }}" @selected(old('new_owner') == $emp->file_no)>
                        {{ $emp->file_no }} - {{ $emp->first_name }} {{ $emp->last_name }}
                    </option>
                @endforeach
            </select>
            @error('new_owner')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
            
            <x-input-label for="status" value="Shift Status" class="mt-4" />
            <select name="status" class="block w-full border rounded">
                @foreach (['None', 'Working', 'Damaged'] as $option)
                    <option value="{{ $option }}" @selected(old('status') == $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Remarks -->
            <x-input-label for="remarks" value="Remarks" class="mt-4" />
            <textarea name="remarks" id="remarks" class="block w-full">{{ old('remarks') }}</textarea>
            @error('remarks')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Requires IT Remark -->
            <label class="flex items-center mt-4">
                <input type="checkbox" name="requires_it_remark" id="requires_it_remark" class="mr-2"
                       {{ old('requires_it_remark') ? 'checked' : '' }}>
                Requires IT Remark
            </label>

            <!-- Submit -->
            <div class="mt-4 flex space-x-2">
                <x-primary-button class="bg-cyan-500 hover:bg-cyan-400">
                    Submit Shift
                </x-primary-button>
                <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    Cancel
                </a>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const checkbox = document.getElementById('requires_it_remark');
                    const remarks = document.getElementById('remarks');

                    const updateRemarksUI = () => {
                        if (checkbox.checked) {
                            remarks.value = '';
                            remarks.style.display = 'none';
                        } else {
                            remarks.style.display = 'block';
                        }
                    };

                    checkbox.addEventListener('change', updateRemarksUI);
                    updateRemarksUI();
                });
            </script>
        </form>
    </div>
</x-app-layout>
