<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($department) ? 'Update Department: ' . $department->department : 'Add Department' }}
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

        <form method="POST" action="{{ isset($department) ? route('departments.update', $department->department) : route('departments.store') }}">
            @csrf
            @if(isset($department)) @method('PUT') @endif

            <x-input-label for="department" value="Department Name" />
            <x-text-input name="department" class="block w-full" value="{{ old('department', $department->department ?? '') }}" />
            @error('department')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex space-x-2">
                <x-primary-button
                    class="{{ isset($department)
                        ? 'bg-blue-600 hover:bg-blue-800'
                        : 'bg-green-800 hover:bg-green-600' }}">
                    {{ isset($department) ? 'Update Department' : 'Add Department' }}
                </x-primary-button>
                <a href="{{ route('departments.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
