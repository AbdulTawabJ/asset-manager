<!DOCTYPE html>
<html>
<head>
    <title>Asset Report</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2, h4 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .download-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Asset Report</h2>
    <h4>Report Filters:
        @foreach ($filters as $key => $value)
            @if (!empty($value))
                <span><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong>: {{ $value }}</span>&nbsp;
            @endif
        @endforeach
    </h4>
    

    @if ($assets->isEmpty())
        <p>No matching assets found.</p>
    @else
    <div class="download-btn">
        <form method="GET" action="{{ route('assets.report.export') }}" target="_blank">
    @foreach ($filters as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    {{-- Also pass sublocation filters explicitly --}}
    <input type="hidden" name="region" value="{{ request('region') }}">
    <input type="hidden" name="branch" value="{{ request('branch') }}">
    <input type="hidden" name="office" value="{{ request('office') }}">
    <input type="hidden" name="department" value="{{ request('department') }}">

    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Download CSV
    </button>
</form>


        </div>
    <table>
    <thead>
        <tr>
            <th>Asset Tag</th>
            <th>Serial</th>
            <th>Date of Purchase</th>
            <th>Date of Issue</th>
            <th>Type</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Location</th>
            <th>Owner</th>
            <th>Remarks</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($assets as $asset)
            <tr>
                <td>{{ $asset->asset_tag }}</td>
                <td>{{ $asset->serial }}</td>
                <td>{{ $asset->date_of_purchase }}</td>
                <td>{{ $asset->date_of_issue }}</td>
                <td>{{ $asset->type }}</td>
                <td>{{ $asset->description }}</td>
                <td>{{ $asset->amount }}</td>
                <td>{{ $asset->location }}</td>
                <td>{{ $asset->owner }}</td>
                <td>{{ $asset->remarks }}</td>
                <td>{{ $asset->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


        

    @endif
</body>
</html>
