<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            IT Dashboard
        </h2>
    </x-slot>

<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 bg-gray-700 mt-6 rounded-xl divide-x divide-gray-800 flex flex-col lg:flex-row gap-6">

        <!-- Transfers Section -->
        <div class="w-full lg:w-1/2 space-y-4">
            <h3 class="text-lg font-semibold text-white mb-4"><i class="fa-solid fa-hand-holding-hand pr-2"></i>Transfers</h3>
            @forelse($pendingShifts as $shift)
            <div class="bg-white shadow-md rounded-lg p-4 mb-4 ml-3">
                <div class="mb-2 text-sm text-gray-800">
                    <strong>Serial No:</strong> {{ $shift->serial_no }}<br>
                    <strong>Description:</strong> {{ $shift->description }}<br>
                    <strong>From:</strong> {{ $shift->prev_owner }} at {{ $shift->prev_location }}<br>
                    <strong>To:</strong> {{ $shift->new_owner }} at {{ $shift->new_location }}<br>
                    <strong>Date:</strong> {{ $shift->date }}
                </div>
                <form action="{{ route('it.remark.shift', $shift->id) }}" method="POST" class="flex ">
                    @csrf
                    <input type="text" name="remark" placeholder="Enter your remark..." class="flex-1 border rounded-left p-2 text-sm">
                    <x-primary-button class="bg-cyan-700 hover:bg-cyan-500 text-sm rounded-right">Remark</x-primary-button>
                </form>
            </div>
            @empty
                <p class="text-gray-400 text-sm  ml-2">No Requests, Nothing to see here.</p>
            @endforelse
        </div>

        <!-- Assets Section -->
        <div class="w-full lg:w-1/2 space-y-4">
            <h3 class="text-lg font-semibold text-white mb-4"><i class="fa-solid fa-gem mr-1 ml-2"></i>Assets</h3>
            @forelse($pendingAssets as $asset)
                <div class="bg-white shadow-md rounded-lg p-4 mb-4 ml-5">
                    <div class="mb-2 text-sm text-gray-800">
                        <strong>Serial No:</strong> {{ $asset->serial_no }}<br>
                        <strong>Description:</strong> {{ $asset->description }}<br>
                        <strong>Location:</strong> {{ $asset->location }}<br>
                        <strong>Owner:</strong> {{ $asset->owner }}<br>
                        <strong>Remarks:</strong> {{ $asset->remarks }}
                    </div>
                    <form action="{{ route('it.remark.asset', $asset->id) }}" method="POST" class="flex ">
                        @csrf
                        <input type="text" name="remark" placeholder="Enter your remark..." class="flex-1 border rounded-left p-2 text-sm">
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-sm rounded-right">Remark</x-primary-button>
                    </form>
                </div>
            @empty
                <p class="text-gray-400 text-sm ml-4">No requests for remarks on Assets.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
