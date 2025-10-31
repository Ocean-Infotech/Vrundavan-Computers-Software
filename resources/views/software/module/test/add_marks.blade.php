@extends('software.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
             <div class="card-header">
           <div class="row align-items-center">
    <div class="col-md-3">
        <h4 class="mb-0">{{ $modules['title'] }} Mark Entry Form</h4>
    </div>
    <div class="col-md-3">
        <strong>Batch:</strong> {{ $test->batch->batch_name ?? 'N/A' }}
    </div>
    <div class="col-md-3">
        <strong>Subject:</strong> {{ $test->subject_name ?? 'N/A' }}
    </div>
    <div class="col-md-3">
        <strong>Unit:</strong> {{ $test->unit_name ?? 'N/A' }}
    </div>
</div>

        </div>

            <form action="{{ route('test.save-marks') }}" method="POST">
                @csrf
                <input type="hidden" name="test_id" value="{{ $test->id }}">

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Mark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->admission->first_name }} {{ $student->admission->last_name }}</td>
                                <td>
                                    <input type="number" class="form-control mark-input"
                                        data-student-id="{{ $student->id }}" data-test-id="{{ $test->id }}"
                                        value="{{ $student->admission->testMarks->isNotEmpty() ? number_format($student->admission->testMarks->first()->marks, 2) : '' }}"
                                        min="0" max="100" placeholder="Enter marks">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">Save Marks</button>
                    <a href="{{ route('test.index') }}" class="btn btn-secondary">Back</a>
                </div> --}}
            </form>
        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mark-input').forEach(function(input) {
                input.addEventListener('change', function() {
                    let mark = parseFloat(this.value);
                    let studentId = this.dataset.studentId;
                    let testId = this.dataset.testId;
                    let maxMark = parseFloat("{{ $test->mark }}"); // maximum allowed mark

                    // ✅ check before saving
                    if (mark > maxMark) {
                        toastr.error("Marks cannot exceed maximum allowed.", "Invalid Marks");
                        this.value = ''; // clear invalid input
                        return;
                    }

                    fetch("{{ route('test.save-marks-ajax') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                student_id: studentId,
                                test_id: testId,
                                mark: mark
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success("Marks saved successfully!", "Success");
                                setTimeout(() => this.style.backgroundColor = "", 1000);
                            } else {
                                toastr.error(data.message || "Something went wrong!", "Error");
                            }
                        })
                        .catch(err => {
                            toastr.error("Failed to save mark. Please try again.", "Error");
                        });
                });
            });
        });
    </script>
@endsection
