<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($location) ? 'Edit Location: ' . $location->location : 'Add Location' }}
        </h2>
    </x-slot>
    @php
    $parts = explode('-', $location->location ?? '');
@endphp

    <div class="py-6 max-w-xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 rounded p-4 text-sm text-red-700">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($location) ? route('locations.update', $location->location) : route('locations.store') }}">

            @csrf
            @if(isset($location)) @method('PUT') @endif

            <!-- Region -->
            <x-input-label for="region" value="Region" />
            <x-text-input name="region" class="block w-full" value="{{ old('region', $parts[0] ?? '') }}" pattern="^[^/-]+$" />
            @error('region')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror

            <!-- Area -->
            <x-input-label for="area" value="Area" class="mt-4" />
            <x-text-input name="area" class="block w-full" value="{{ old('area', $parts[1] ?? '') }}" pattern="^[^/-]+$" />
            @error('area')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror

            <!-- Branch -->
            <x-input-label for="branch" value="Branch" class="mt-4" />
            <x-text-input name="branch" class="block w-full" value="{{ old('branch', $parts[2] ?? '') }}" pattern="^[^/-]+$" />
            @error('branch')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror

            <!-- Department -->
            <x-input-label for="department" value="Department" class="mt-4" />
            <x-text-input name="department" class="block w-full" value="{{ old('department', $parts[3] ?? '') }}" pattern="^[^/-]+$" />
            @error('department')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            
            <div class="mt-4 flex space-x-2">
<x-primary-button
    class="{{ isset($location)
        ? 'bg-blue-600 hover:bg-blue-800'
        : 'bg-green-800 hover:bg-green-600' }}">
    {{ isset($location) ? 'Update Location' : 'Add Location' }}
</x-primary-button>
                <a href="{{ route('locations.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">Cancel</a>
            </div>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const regionInput = document.querySelector('input[name="region"]');
        const areaInput = document.querySelector('input[name="area"]');
        const branchInput = document.querySelector('input[name="branch"]');
        const departmentInput = document.querySelector('input[name="department"]');

        function updateState() {
            areaInput.disabled = !regionInput.value.trim();
            branchInput.disabled = !areaInput.value.trim();
            departmentInput.disabled = !branchInput.value.trim();
        }

        // Bind events
        regionInput.addEventListener('input', updateState);
        areaInput.addEventListener('input', updateState);
        branchInput.addEventListener('input', updateState);

        // Run once on load
        updateState();
    });
</script>

</x-app-layout>
