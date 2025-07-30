<table class="table table-striped table-hover dt-responsive nowrap" style="width: 100%;">
    @if (isset($columns))
        <thead>
            <tr>
                @foreach ($columns as $item)
                    <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
                @endforeach
            </tr>
        </thead>
    @endif
    @if ($data)
    @foreach ($data as $item)
    <tr>
                @php
                    $item = (object) $item;
                @endphp
                <td>{{ $item?->Name ?? '' }}</td>
                <td>{{ $item?->DeviceKey ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($item?->LastUpdatedOn)->format('d-m-Y h:i A') ?? '' }}</td>
                <td>{{ $item?->LogCount ?? '-Logcount-' }}</td>
                <td>{{ $item?->UserCount ?? '-UserCount-' }}</td>
                <td>{{ $item?->FaceCount ?? '-FaceCount-' }}</td>
                <td>{{ $item?->FPCount ?? '-FPCount-' }}</td>
                <td>{{ $item?->PalmCount ?? '-PlamCount-' }}</td>
                <td>{{ $item?->Location ?? '-LocationCount-' }}</td>
                {{-- <td>{{ $item?->FPCount ?? '-LeaveStatus-' }}</td> --}}
            </tr>
            @endforeach
    @endif
</table>
