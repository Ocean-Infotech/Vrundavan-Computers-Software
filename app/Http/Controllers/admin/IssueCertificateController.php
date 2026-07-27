<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\IssueCertificateHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class IssueCertificateController extends Controller
{
    public $modules = [];

    private const TYPE_BONAFIDE = 'bonafide';
    private const TYPE_TC = 'tc';
    private const TYPE_ENGLISH_MEDIUM = 'english-medium';
    private const TYPE_LETTER_RECOMMENDATION = 'letter-recommendation';

    public function __construct()
    {
        $this->modules = [
            'folder_path' => 'software.module.issue-certificate',
        ];
    }

    public function bonafideIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-bonafide-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, 'bonafide');
    }

    public function tcIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-tc-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, 'tc');
    }

    public function englishMediumIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-english-medium-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, self::TYPE_ENGLISH_MEDIUM);
    }

    public function letterRecommendationIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-letter-recommendation-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, self::TYPE_LETTER_RECOMMENDATION);
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'certificate_type' => 'required|in:bonafide,tc,english-medium,letter-recommendation',
            'student_id' => 'required|exists:admission,id',
            'issue_date' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'semester_start' => 'nullable|string',
            'semester_end' => 'nullable|string',
        ]);

        $permission = match ($validated['certificate_type']) {
            self::TYPE_TC => 'issue-certificate-tc-list',
            self::TYPE_ENGLISH_MEDIUM => 'issue-certificate-english-medium-list',
            self::TYPE_LETTER_RECOMMENDATION => 'issue-certificate-letter-recommendation-list',
            default => 'issue-certificate-bonafide-list',
        };

        if (!Helper::directCan($permission)) {
            return response()->json([
                'status' => false,
                'message' => 'User does not have the right permissions.',
            ], 403);
        }

        if (!Schema::hasTable('issue_certificate_histories')) {
            return response()->json([
                'status' => false,
                'message' => 'History table not found.',
            ], 422);
        }

        $admission = Admission::findOrFail($validated['student_id']);
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );

        $history = IssueCertificateHistory::create([
            'certificate_type' => $validated['certificate_type'],
            'register_id' => $admission->id,
            'student_name' => $studentName,
            'issue_date' => $validated['issue_date'] ?? null,
            'academic_year' => $validated['academic_year'] ?? null,
            'semester_start' => $validated['semester_start'] ?? null,
            'semester_end' => $validated['semester_end'] ?? null,
            'created_by' => optional(auth()->user())->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Certificate history saved.',
            'data' => $this->formatHistoryRow($history, $validated['certificate_type']),
        ]);
    }

    protected function certificateIndex(Request $request, string $type)
    {
        $typeLabel = match ($type) {
            self::TYPE_ENGLISH_MEDIUM => 'Medium of Instruction',
            self::TYPE_LETTER_RECOMMENDATION => 'Letter of Recommendation',
            self::TYPE_TC => 'Transfer Certificate',
            default => 'Bonafide Certificate',
        };

        $modules = [
            'title' => $typeLabel,
            'folder_path' => 'software.module.issue-certificate',
            'route' => 'issue-certificate.' . $type,
            'certificate_type' => $type,
            'certificate_route' => match ($type) {
                self::TYPE_TC => 'transfer-certificate',
                self::TYPE_ENGLISH_MEDIUM => 'english-medium-certificate',
                self::TYPE_LETTER_RECOMMENDATION => 'letter-of-recommendation',
                default => 'bonafide-certificate',
            },
        ];

        View::share('modules', $modules);

        $students = $this->getStudentOptions();
        $defaults = $this->getCertificateDefaults();
        $histories = $this->getHistories($type);

        return view($modules['folder_path'] . '.index', compact('modules', 'students', 'defaults', 'histories'));
    }

    private function getStudentOptions()
    {
        return Admission::orderByDesc('id')
            ->get()
            ->map(function ($admission) {
                $name = trim(
                    ($admission->first_name ?? '') . ' ' .
                    ($admission->last_name ?? '') . ' ' .
                    ($admission->father_name ?? '')
                );

                return [
                    'id' => $admission->id,
                    'label' => trim($admission->id . ' - ' . $name),
                ];
            })
            ->values();
    }

    private function getCertificateDefaults(): array
    {
        $today = now();

        return [
            'issue_date' => $today->format('Y-m-d'),
            'academic_year' => $today->format('Y'),
        ];
    }

    private function getHistories(string $type)
    {
        if (!Schema::hasTable('issue_certificate_histories')) {
            return collect();
        }

        return IssueCertificateHistory::where('certificate_type', $type)
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(function ($history) use ($type) {
                return $this->formatHistoryRow($history, $type);
            });
    }

    private function formatHistoryRow(IssueCertificateHistory $history, string $type): array
    {
        $certificateRoute = match ($type) {
            self::TYPE_TC => 'transfer-certificate',
            self::TYPE_ENGLISH_MEDIUM => 'english-medium-certificate',
            self::TYPE_LETTER_RECOMMENDATION => 'letter-of-recommendation',
            default => 'bonafide-certificate',
        };

        $params = http_build_query(array_filter([
            'issue_date' => $history->issue_date,
            'academic_year' => $history->academic_year,
            'semester_start' => $history->semester_start,
            'semester_end' => $history->semester_end,
        ]));

        $certificateUrl = route($certificateRoute, $history->register_id) . ($params ? '?' . $params : '');

        return [
            'id' => $history->id,
            'register_id' => $history->register_id,
            'student_name' => $history->student_name ?: '-',
            'issue_date' => $this->formatDisplayDate($history->issue_date),
            'academic_year' => $history->academic_year ?: '-',
            'semester_start' => $history->semester_start ?: '-',
            'semester_end' => $history->semester_end ?: '-',
            'generated_at' => $history->created_at
                ? $history->created_at->format('d-m-Y h:i A')
                : '-',
            'certificate_url' => $certificateUrl,
        ];
    }

    private function formatDisplayDate(?string $value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j-m-Y');
        } catch (\Exception $e) {
            return $value;
        }
    }
}
