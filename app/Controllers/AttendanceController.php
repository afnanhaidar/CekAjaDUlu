<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\RedirectResponse;
use DateInterval;
use DateTimeImmutable;
use Throwable;

class AttendanceController extends BaseController
{
    private const STATUSES = ['Hadir', 'Sakit', 'Izin', 'Alpa'];

    public function index(): string
    {
        $students = (new StudentModel())->getWeeklyStudents();
        $dates = $this->getWeeklyDates();
        $dateValues = array_column($dates, 'value');
        $studentIds = array_map(
            static fn (array $student): int => (int) $student['id'],
            $students
        );

        $matrix = (new AttendanceModel())->getStatusMatrix(
            $studentIds,
            $dateValues[0],
            $dateValues[count($dateValues) - 1]
        );

        $totals = [];

        foreach ($studentIds as $studentId) {
            $statuses = $matrix[$studentId] ?? [];
            $totals[$studentId] = count(
                array_filter(
                    $statuses,
                    static fn (string $status): bool => $status === 'Hadir'
                )
            );
        }

        return view('attendance_view', [
            'students' => $students,
            'dates' => $dates,
            'matrix' => $matrix,
            'totals' => $totals,
            'statuses' => self::STATUSES,
        ]);
    }

    public function save(): RedirectResponse
    {
        $students = (new StudentModel())->getWeeklyStudents();
        $studentIds = array_map(
            static fn (array $student): int => (int) $student['id'],
            $students
        );
        $dates = $this->getWeeklyDates();
        $allowedDates = array_flip(array_column($dates, 'value'));
        $attendance = $this->request->getPost('attendance');

        if (!is_array($attendance)) {
            return redirect()->to(site_url('presensi'))
                ->withInput()
                ->with('error', 'Data presensi tidak valid.');
        }

        $records = [];

        foreach ($studentIds as $studentId) {
            $studentAttendance = $attendance[$studentId] ?? $attendance[(string) $studentId] ?? null;

            if (!is_array($studentAttendance)) {
                return redirect()->to(site_url('presensi'))
                    ->withInput()
                    ->with('error', 'Data presensi setiap murid harus dilengkapi.');
            }

            foreach (array_keys($allowedDates) as $date) {
                $status = $studentAttendance[$date] ?? null;

                if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
                    return redirect()->to(site_url('presensi'))
                        ->withInput()
                        ->with('error', 'Status presensi yang dipilih tidak valid.');
                }

                $records[] = [
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                    'status' => $status,
                ];
            }
        }

        try {
            (new AttendanceModel())->upsertBatch($records);
        } catch (Throwable) {
            return redirect()->to(site_url('presensi'))
                ->withInput()
                ->with('error', 'Presensi gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->to(site_url('presensi'))
            ->with('success', 'Presensi berhasil disimpan!');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function getWeeklyDates(): array
    {
        $today = new DateTimeImmutable('today');
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $dates = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $date = $today->add(new DateInterval('P' . $offset . 'D'));
            $dates[] = [
                'value' => $date->format('Y-m-d'),
                'label' => $days[$date->format('l')] . ', ' . $date->format('d/m'),
            ];
        }

        return $dates;
    }
}
