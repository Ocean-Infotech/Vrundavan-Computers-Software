@php
    $hideExtraColumns = $hideExtraColumns ?? false;
@endphp

@forelse(($histories ?? collect()) as $index => $history)
    <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $history['student_name'] ?? '-' }}</td>
        <td>{{ $history['issue_date'] ?? '-' }}</td>
        @unless($hideExtraColumns)
            <td>{{ $history['academic_year'] ?? '-' }}</td>
            <td>{{ $history['semester_start'] ?? '-' }}</td>
            <td>{{ $history['semester_end'] ?? '-' }}</td>
            <td>{{ $history['generated_at'] ?? '-' }}</td>
        @endunless
        <td class="text-center">
            <a href="{{ $history['certificate_url'] ?? '#' }}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                <i class="ti ti-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr id="certificate-history-empty-row">
        <td colspan="{{ $hideExtraColumns ? 4 : 8 }}" class="text-center text-muted">No certificate generated yet.</td>
    </tr>
@endforelse
