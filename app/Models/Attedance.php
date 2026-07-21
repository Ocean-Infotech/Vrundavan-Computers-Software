<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Attedance extends Model
{
    public $table = 'attedance';

    public $timestamps = false;

    protected $fillable = [
        'biometric_id',
        'admission_id',
        'date',
        'in_time',
        'out_time',
        'DeviceKey',
        'DeviceName',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id', 'id');
    }

    public static function sortedPunchesForDate($admissionId, string $date, ?string $biometricId = null): Collection
    {
        return static::where(function ($query) use ($admissionId, $biometricId) {
                $query->where('admission_id', $admissionId);
                if (!empty($biometricId)) {
                    $query->orWhere('biometric_id', $biometricId);
                }
            })
            ->whereDate('date', $date)
            ->orderBy('in_time')
            ->orderBy('id')
            ->get();
    }

    public static function resolveDayAttendance(Collection $punches): array
    {
        $sorted = $punches->sortBy(fn ($punch) => $punch->in_time . '_' . $punch->id)->values();

        $inTime = $sorted->first()?->in_time;
        $outTime = $sorted->count() >= 2 ? $sorted->get(1)?->in_time : null;

        return [
            'in_time' => $inTime,
            'out_time' => $outTime,
            'is_present' => $sorted->isNotEmpty(),
            'punch_count' => $sorted->count(),
            'punches' => $sorted->map(function ($punch, $index) {
                return [
                    'id' => $punch->id,
                    'biometric_id' => $punch->biometric_id,
                    'time' => $punch->in_time,
                    'type' => ($index % 2 === 0) ? 'In' : 'Out',
                    'device_key' => $punch->DeviceKey,
                    'device_name' => $punch->DeviceName,
                ];
            })->values()->all(),
        ];
    }

    public static function formatTimeForApi(?string $time): ?string
    {
        if (empty($time)) {
            return null;
        }

        return date('h:i A', strtotime($time));
    }
}
