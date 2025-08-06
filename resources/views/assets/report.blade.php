<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-start gap-2">
            <a href="{{ route('admin.dashboard') }}" class="transition ease-in bg-gray-100 hover:bg-gray-600 text-gray-900 hover:text-white shadow px-3 py-2 text-sm rounded">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Report Generator - Assets
            </h2>
            <a href="{{ route('assets.query') }}" class="ms-auto bg-gray-100 hover:bg-purple-700 hover:text-white text-gray-700 px-4 py-2 rounded text-sm shadow hover:shadow-xl transition ease-in">
                            <i class="fa-solid fa-filter"></i> Advanced Filters
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="GET" action="{{ route('assets.report.generate') }}" target="reportWindow" onsubmit="openReportWindow()">
    <div class="mb-4">
        <label for="asset_tag" class="block text-gray-900  font-medium mb-2">Asset Tag</label>
        <input type="text" id="asset_tag" name="asset_tag" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm" placeholder="TMF/FU/...">
    </div>

    <div class="mb-4">
        <label for="serial" class="block text-gray-900  font-medium mb-2">Serial</label>
        <input type="text" id="serial" name="serial" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm" placeholder="XXXX">
    </div>
    <div class="mb-4">
    <label class="block text-gray-900  font-medium mb-2">Date of Purchase Range</label>
    <div class="flex gap-2">
        <input type="date" name="purchase_start" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <input type="date" name="purchase_end" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
    </div>
</div>

<div class="mb-4">
    <label class="block text-gray-900  font-medium mb-2">Date of Issue Range</label>
    <div class="flex gap-2">
        <input type="date" name="issue_start" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <input type="date" name="issue_end" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
    </div>
</div>
<div class="mb-4">
    <label class="block text-gray-900  font-medium mb-2">Amount Range</label>
    <div class="flex gap-2">
        <input type="number" step="0.01" name="amount_min" placeholder="Min" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <input type="number" step="0.01" name="amount_max" placeholder="Max" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
    </div>
</div>
<div class="mb-4">
    <label for="status" class="block text-gray-900  font-medium mb-2">Status</label>
    <select name="status" id="status" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <option value="">-- Select Status --</option>
        <option value="None">None</option>
        <option value="Working">Working</option>
        <option value="Damaged">Damaged</option>
    </select>
</div>

<!-- Owner Dropdown -->
<div class="mb-4">
    <label for="owner" class="block text-gray-900  font-medium mb-2">Owner (File No)</label>
    <select name="owner" id="owner" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <option value="">-- Select Owner --</option>
        @foreach ($owners as $owner)
            <option value="{{ $owner }}">{{ $owner }}</option>
        @endforeach
    </select>
</div>

<!-- Type Dropdown -->
<div class="mb-4">
    <label for="type" class="block text-gray-900  font-medium mb-2">Type</label>
    <select name="type" id="type" class="w-full border-gray-300 focus:ring-cyan-600 focus:border-cyan-600 rounded-md shadow-sm">
        <option value="">-- Select Type --</option>
        @foreach ($types as $type)
            <option value="{{ $type }}">{{ $type }}</option>
        @endforeach
    </select>
</div>

<!-- Location Dropdown -->
<!-- Location Filters (Region → Branch → Office → Department) -->
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Location</label>
    <div class="flex flex-col md:flex-row gap-2">
        <!-- Region -->
        <select name="region" id="region" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Select Region --</option>
            @foreach ($locations['regions'] as $region)
                <option value="{{ $region }}">{{ $region }}</option>
            @endforeach
        </select>

        <!-- Branch -->
        <select name="branch" id="branch" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Branch --</option>
        </select>

        <!-- Office -->
        <select name="office" id="office" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Office --</option>
        </select>

        <!-- Department -->
        <select name="department" id="department" class="w-full border-gray-300 rounded-md shadow-sm" disabled>
            <option value="">-- Select Department --</option>
        </select>
    </div>
</div>


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

    const regionSelect = document.getElementById('region');
    const branchSelect = document.getElementById('branch');
    const officeSelect = document.getElementById('office');
    const departmentSelect = document.getElementById('department');

    regionSelect.addEventListener('change', function () {
        const selectedRegion = this.value;
        branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
        officeSelect.innerHTML = '<option value="">-- Select Office --</option>';
        departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
        branchSelect.disabled = true;
        officeSelect.disabled = true;
        departmentSelect.disabled = true;

        if (!selectedRegion) return;

        // Populate branches
        const filteredBranches = branches.filter(b => b.startsWith(selectedRegion + '-'));
        filteredBranches.forEach(branch => {
            const value = branch.split('-')[1]; // just branch name
            branchSelect.innerHTML += `<option value="${branch}">${value}</option>`;
        });
        branchSelect.disabled = false;
    });

    branchSelect.addEventListener('change', function () {
        const selectedRegion = regionSelect.value;
        const selectedBranch = this.value;
        officeSelect.innerHTML = '<option value="">-- Select Office --</option>';
        departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
        officeSelect.disabled = true;
        departmentSelect.disabled = true;

        if (!selectedBranch) return;

        // Populate offices
        const filteredOffices = offices.filter(o => o.startsWith(selectedBranch + '-'));
        filteredOffices.forEach(office => {
            const value = office.split('-')[2]; // just office name
            officeSelect.innerHTML += `<option value="${office}">${value}</option>`;
        });
        officeSelect.disabled = false;
    });

    officeSelect.addEventListener('change', function () {
        const selectedOffice = this.value;
        departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
        departmentSelect.disabled = true;

        if (!selectedOffice) return;

        // Populate departments
        const filteredDepartments = departments.filter(d => d.startsWith(selectedOffice + '-'));
        filteredDepartments.forEach(dept => {
            const value = dept.split('-')[3]; // just department name
            departmentSelect.innerHTML += `<option value="${dept}">${value}</option>`;
        });
        departmentSelect.disabled = false;
    });
</script>

</x-app-layout>
