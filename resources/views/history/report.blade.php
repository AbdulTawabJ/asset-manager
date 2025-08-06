<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-start gap-2">
            <a href="{{ route('history.index') }}" class="transition ease-in bg-gray-100 hover:bg-gray-600 text-gray-900 hover:text-white shadow px-3 py-2 text-sm rounded">
                <i class="fa-solid fa-arrow-left"></i> Back to Movement History
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Report Generator - Asset History
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="GET" action="{{ route('history.report.generate') }}" target="reportWindow" onsubmit="openReportWindow()">
                    <!-- Asset Tag -->
                    <div class="mb-4">
                        <label for="asset_tag" class="block text-gray-900 font-medium mb-2">Asset Tag</label>
                        <input type="text" id="asset_tag" name="asset_tag" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm" placeholder="TMF/FU/...">
                    </div>

                    <!-- Date Range -->
                    <div class="mb-4">
                        <label class="block text-gray-900 font-medium mb-2">Date Range</label>
                        <div class="flex gap-2">
                            <input type="date" name="date_start" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
                            <input type="date" name="date_end" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="block text-gray-900 font-medium mb-2">Status</label>
                        <select name="status" id="status" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
                            <option value="">-- Select Status --</option>
                            <option value="None">None</option>
                            <option value="Working">Working</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>

                    <!-- Previous Owner -->
                    <div class="mb-4">
                        <label for="prev_owner" class="block text-gray-900 font-medium mb-2">Previous Owner (File No)</label>
                        <select name="prev_owner" id="prev_owner" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
                            <option value="">-- Select Owner --</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner }}">{{ $owner }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- New Owner -->
                    <div class="mb-4">
                        <label for="new_owner" class="block text-gray-900 font-medium mb-2">New Owner (File No)</label>
                        <select name="new_owner" id="new_owner" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
                            <option value="">-- Select Owner --</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner }}">{{ $owner }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Previous Location Filter -->
<div class="mb-4">
    <label class="block text-gray-900 font-medium mb-2">Previous Location</label>
    <div class="flex flex-col md:flex-row gap-2">
        <select name="prev_region" id="prev_region" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Select Region --</option>
            @foreach ($locations['regions'] as $region)
                <option value="{{ $region }}">{{ $region }}</option>
            @endforeach
        </select>

        <select name="prev_branch" id="prev_branch" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Branch --</option>
        </select>

        <select name="prev_office" id="prev_office" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Office --</option>
        </select>

        <select name="prev_location" id="prev_department" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Department --</option>
        </select>
    </div>
</div>

<!-- New Location Filter -->
<div class="mb-4">
    <label class="block text-gray-900 font-medium mb-2">New Location</label>
    <div class="flex flex-col md:flex-row gap-2">
        <select name="new_region" id="new_region" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Select Region --</option>
            @foreach ($locations['regions'] as $region)
                <option value="{{ $region }}">{{ $region }}</option>
            @endforeach
        </select>

        <select name="new_branch" id="new_branch" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Branch --</option>
        </select>

        <select name="new_office" id="new_office" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Office --</option>
        </select>

        <select name="new_location" id="new_department" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Department --</option>
        </select>
    </div>
</div>


                    <!-- Submit Button -->
                    <button type="submit" class="transition ease-in text-white hover:bg-cyan-600 bg-cyan-800 shadow hover:shadow-xl px-6 py-2 rounded shadow">
                        Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReportWindow() {
            window.open('', 'reportWindow', 'width=1000,height=700');
        }
    </script>
    <script>
    const branches = @json($locations['branches']);
    const offices = @json($locations['offices']);
    const departments = @json($locations['departments']);

    // Utility function for populating select boxes
    function populateNextLevel(currentValue, nextSelect, allOptions, levelIndex) {
        nextSelect.innerHTML = '<option value="">-- Select --</option>';
        nextSelect.disabled = true;
        if (!currentValue) return;

        const filtered = allOptions.filter(item => item.startsWith(currentValue + '-'));
        filtered.forEach(option => {
            const value = option.split('-')[levelIndex];
            nextSelect.innerHTML += `<option value="${option}">${value}</option>`;
        });
        nextSelect.disabled = false;
    }

    // === PREVIOUS LOCATION LOGIC ===
    const prev_region = document.getElementById('prev_region');
    const prev_branch = document.getElementById('prev_branch');
    const prev_office = document.getElementById('prev_office');
    const prev_dept = document.getElementById('prev_department');

    prev_region.addEventListener('change', () => {
        populateNextLevel(prev_region.value, prev_branch, branches, 1);
        prev_office.innerHTML = '<option value="">-- Select Office --</option>'; prev_office.disabled = true;
        prev_dept.innerHTML = '<option value="">-- Select Department --</option>'; prev_dept.disabled = true;
    });

    prev_branch.addEventListener('change', () => {
        populateNextLevel(prev_branch.value, prev_office, offices, 2);
        prev_dept.innerHTML = '<option value="">-- Select Department --</option>'; prev_dept.disabled = true;
    });

    prev_office.addEventListener('change', () => {
        populateNextLevel(prev_office.value, prev_dept, departments, 3);
    });

    // === NEW LOCATION LOGIC ===
    const new_region = document.getElementById('new_region');
    const new_branch = document.getElementById('new_branch');
    const new_office = document.getElementById('new_office');
    const new_dept = document.getElementById('new_department');

    new_region.addEventListener('change', () => {
        populateNextLevel(new_region.value, new_branch, branches, 1);
        new_office.innerHTML = '<option value="">-- Select Office --</option>'; new_office.disabled = true;
        new_dept.innerHTML = '<option value="">-- Select Department --</option>'; new_dept.disabled = true;
    });

    new_branch.addEventListener('change', () => {
        populateNextLevel(new_branch.value, new_office, offices, 2);
        new_dept.innerHTML = '<option value="">-- Select Department --</option>'; new_dept.disabled = true;
    });

    new_office.addEventListener('change', () => {
        populateNextLevel(new_office.value, new_dept, departments, 3);
    });
</script>

</x-app-layout>
