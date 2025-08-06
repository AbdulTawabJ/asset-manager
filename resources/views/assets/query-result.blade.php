<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asset Query Results
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded-lg overflow-x-auto p-4">
            @if(count($assets))
                <table class="min-w-full table-auto text-sm text-left">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            @foreach ($columns as $key => $label)
                                @if (!request('fields') || in_array($key, request('fields')))
                                    <th class="px-4 py-2 border">{{ $label }}</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr class="hover:bg-gray-50">
                                @foreach ($columns as $key => $label)
                                    @if (!request('fields') || in_array($key, request('fields')))
                                        <td class="px-4 py-2 border">{{ $asset->$key ?? '' }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-600 text-sm">No results found for this query.</p>
            @endif
        </div>
    </div>
</x-app-layout>
