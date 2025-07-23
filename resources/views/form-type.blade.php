<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($type) ? 'Edit Asset Type: ' . $type->type : 'Add a New Asset Type' }}
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

        <form method="POST" action="{{ isset($type) ? route('types.update', $type->type) : route('types.store') }}">
            @csrf
            @if(isset($type)) @method('PUT') @endif

            <x-input-label for="type" value="Asset Type's Name" />
            <x-text-input name="type" class="block w-full" value="{{ old('type', $type->type ?? '') }}" />
            @error('type')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex space-x-2">
                <x-primary-button
                    class="{{ isset($type)
                        ? 'bg-blue-600 hover:bg-blue-800'
                        : 'bg-green-800 hover:bg-green-600' }}">
                    {{ isset($type) ? 'Update Asset Type' : 'Add Asset Type' }}
                </x-primary-button>
                <a href="{{ route('types.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
