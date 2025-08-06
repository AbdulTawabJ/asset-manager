<!DOCTYPE html>
<html>
<head>
    <title>Asset History Report</title>
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
    <h2>Asset History Report</h2>
    <h4>Report Filters:
        @foreach ($filters as $key => $value)
            @if (!empty($value))
                <span><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong>: {{ $value }}</span>&nbsp;
            @endif
        @endforeach
    </h4>

    @if ($records->isEmpty())
        <p>No matching history records found.</p>
    @else
        <div class="download-btn">
            <form method="GET" action="{{ route('history.report.export') }}" target="_blank">
                @foreach ($filters as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="hidden" name="prev_region" value="{{ request('prev_region') }}">
<input type="hidden" name="prev_branch" value="{{ request('prev_branch') }}">
<input type="hidden" name="prev_office" value="{{ request('prev_office') }}">
<input type="hidden" name="prev_location" value="{{ request('prev_location') }}">

<input type="hidden" name="new_region" value="{{ request('new_region') }}">
<input type="hidden" name="new_branch" value="{{ request('new_branch') }}">
<input type="hidden" name="new_office" value="{{ request('new_office') }}">
<input type="hidden" name="new_location" value="{{ request('new_location') }}">

                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Download CSV
                </button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Asset Tag</th>
                    <th>Description</th>
                    <th>Previous Location</th>
                    <th>New Location</th>
                    <th>Previous Owner</th>
                    <th>New Owner</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $item)
                    <tr>
                        <td>{{ $item->asset_tag }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->prev_location }}</td>
                        <td>{{ $item->new_location }}</td>
                        <td>{{ $item->prev_owner }}</td>
                        <td>{{ $item->new_owner }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
</body>

</html>
