<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($location) ? 'Edit Location' : 'Add Location' }}
        </h2>
    </x-slot>

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

            <x-input-label for="location" value="Location Name" />
            <x-text-input name="location" class="block w-full" value="{{ old('location', $location->location ?? '') }}" />
            @error('location')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror


            
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
</x-app-layout>
