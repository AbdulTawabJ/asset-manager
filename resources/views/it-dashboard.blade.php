<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            IT Dashboard
        </h2>
    </x-slot>

<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 bg-gray-900 mt-6 rounded-xl divide-x divide-gray-800 flex flex-col lg:flex-row gap-6 justify-evenly">

        <!-- Transfers Section -->
        <div class="w-full lg:w-1/2 space-y-4">
            <h3 class="text-md font-semibold text-white mb-4 text-2xl"><i class="fa-solid fa-hand-holding-hand pr-2 text-cyan-400"></i>Transfers</h3>
            @forelse($pendingShifts as $shift)
            <div class="rounded-lg p-4 mb-4 ml-3 w-50  shadow-lg hover:shadow-xl bg-gray-800 hover:bg-gray-700 transition ease-in">
                <div class="mb-2 text-md text-white">
                    <span class='text-2xl text-cyan-400'>{{ $shift->asset_tag }}</span><br>
                    <span class='text-lg text-cyan-400'>Asset Description</span><br>
                    <span class='text-lg text-white'> {{ $shift->description }}</span><br>
                    <span class='text-lg text-cyan-400'>Shift Description</span><br>
                    <span class='text-lg text-white'> {{ $shift->description }}</span><br>
                    <span class='text-lg text-cyan-400'>From</span><br>
                    <span class='text-lg '><i class="fa-solid fa-user-tie"></i></span> {{ $shift->prev_owner }} <span class='text-lg'><i class="fa-solid fa-location-dot"></i></span> {{ $shift->prev_location }}<br>
                    <span class='text-lg text-cyan-400'>To</span><br>
                    <span class='text-lg '><i class="fa-solid fa-user-tie"></i></span> {{ $shift->new_owner }} <span class='text-lg '><i class="fa-solid fa-location-dot"></i></span> {{ $shift->new_location }}<br>
                    <span class='text-sm text-gray-100'>{{ $shift->date }}</span>
            </div>
                <form action="{{ route('it.remark.shift', $shift->id) }}" method="POST" class="flex flex-col gap-2 w-50">
                    @csrf
                    {{-- Status dropdown on its own line --}}
                    <select name="status" class=" border w-40 bg-gray-600 text-white border-cyan-700 p-2 text-md w-full focus:ring-cyan-600 focus:border-cyan-600">
                        @foreach (['None', 'Working', 'Damaged'] as $option)
                            @php
                                $currentStatus = \App\Models\Asset::where('asset_tag', $shift->asset_tag)->value('status');
                            @endphp
                            <option value="{{ $option }}" @selected($currentStatus === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <div class = 'flex'>
                        <input type="text" name="remark" placeholder="Enter your remark..." class=" focus:ring-cyan-600 focus:border-cyan-600 flex-1 border rounded-none border-cyan-700 p-2 text-md">
                        <x-primary-button class="bg-cyan-600 hover:bg-cyan-500 text-md rounded-none focus:ring-cyan-600 ">Submit</x-primary-button>
                    </div>
                </form>
            </div>
            @empty
                <p class="text-gray-400 text-md  ml-2">No Requests, Nothing to see here.</p>
            @endforelse
        </div>

        <!-- Assets Section -->
        <div class="w-full lg:w-1/2 space-y-4">
            <h3 class="text-md font-semibold text-white text-2xl mb-4"><i class="fa-solid fa-gem mr-1 ml-2 text-blue-500"></i>Assets</h3>
            @forelse($pendingAssets as $asset)
                <div class=" p-4 mb-4 ml-5 shadow-lg hover:shadow-xl bg-gray-800 hover:bg-gray-700 transition ease-in">
                    <div class="mb-2 text-md text-white">
                        <span class='text-2xl text-blue-400'>{{ $asset->asset_tag }}</span><br>
                        <span class='text-lg'>{{ $asset->description }}</span><br>
                        <span class='text-lg text-blue-400'><i class="fa-solid fa-location-dot"></i></span> {{ $asset->location }}<br>
                        <span class='text-lg text-blue-400'><i class="fa-solid fa-user-tie"></i></span> {{ $asset->owner }}<br>
                    </div>
                    <form action="{{ route('it.remark.asset', $asset->id) }}" method="POST" class="flex flex-col gap-2">
                        @csrf
                        {{-- Status dropdown on its own line --}}
                        <select name="status" class="border rounded-none p-2 text-md w-40 bg-gray-600 text-white border-blue-700">
                            @foreach (['None', 'Working', 'Damaged'] as $option)
                                <option value="{{ $option }}" @selected($asset->status === $option)>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                        <div class="flex ">
                        <input type="text" name="remark" placeholder="Enter your remark..." class=" border-blue-100 flex-1 border p-2 text-md">
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-md rounded-none">Submit</x-primary-button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-gray-400 text-md ml-4">No requests for remarks on Assets.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
