<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($employee) ? 'Edit Employee: ' . $employee->file_no . ' - ' . $employee->first_name : 'Add Employee' }}
            
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

        <form method="POST" action="{{ isset($employee) ? route('employees.update', $employee->file_no) : route('employees.store') }}">
            @csrf
            @if(isset($employee)) @method('PUT') @endif

            <x-input-label for="file_no" value="File No" />
            <x-text-input name="file_no" class="block w-full" value="{{ old('file_no', $employee->file_no ?? '') }}" />
            @error('file_no')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <x-input-label for="first_name" value="First Name" class="mt-4" />
            <x-text-input name="first_name" class="block w-full" value="{{ old('first_name', $employee->first_name ?? '') }}" />
            @error('first_name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <x-input-label for="middle_name" value="Middle Name" class="mt-4" />
            <x-text-input name="middle_name" class="block w-full" value="{{ old('middle_name', $employee->middle_name ?? '') }}" />
            @error('middle_name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <x-input-label for="last_name" value="Last Name" class="mt-4" />
            <x-text-input name="last_name" class="block w-full" value="{{ old('last_name', $employee->last_name ?? '') }}" />
            @error('last_name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <x-input-label for="email" value="Email" class="mt-4" />
            <x-text-input type="email" name="email" class="block w-full" value="{{ old('email', $employee->email ?? '') }}" />
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <x-input-label for="department" value="Department" class="mt-4" />
            <select name="department" class="block w-full border-gray-300 rounded-md shadow-sm">
                @foreach ($departments as $dept)
                    <option value="{{ $dept->department }}" {{ old('department', $employee->department ?? '') === $dept->department ? 'selected' : '' }}>
                        {{ $dept->department }}
                    </option>
                @endforeach
            </select>
            @error('department')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror


            <div class="mt-4 flex space-x-2">
                <x-primary-button
                    class="{{ isset($employee) ? 'bg-blue-600 hover:bg-blue-800' : 'bg-green-800 hover:bg-green-600' }}">
                    {{ isset($employee) ? 'Update Employee' : 'Add Employee' }}
                </x-primary-button>
                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
