<?php

use App\Controllers\AttendanceController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AttendanceControllerTest extends CIUnitTestCase
{
    public function testBuildsSevenDatesStartingToday(): void
    {
        $controller = new TestableAttendanceController();
        $dates = $controller->dateRange(
            new \DateTimeImmutable('2026-11-06', new \DateTimeZone('Asia/Jakarta'))
        );

        $this->assertSame([
            '2026-11-06',
            '2026-11-07',
            '2026-11-08',
            '2026-11-09',
            '2026-11-10',
            '2026-11-11',
            '2026-11-12',
        ], $dates);
    }

    public function testCountsOnlyPresentStatuses(): void
    {
        $controller = new TestableAttendanceController();

        $total = $controller->presentTotal([
            '2026-11-06' => 'Hadir',
            '2026-11-07' => 'Sakit',
            '2026-11-08' => 'Hadir',
            '2026-11-09' => 'Izin',
            '2026-11-10' => 'Alpa',
            '2026-11-11' => 'Hadir',
        ]);

        $this->assertSame(3, $total);
    }

    public function testRetainsValidAttendanceWhenOtherCellsAreInvalid(): void
    {
        $controller = new TestableAttendanceController();

        $prepared = $controller->prepareRecords(
            [
                1 => [
                    '2026-11-06' => 'Hadir',
                    '2026-11-07' => 'Terlambat',
                    '2026-11-15' => 'Sakit',
                ],
                99 => [
                    '2026-11-06' => 'Izin',
                ],
            ],
            [1 => 0],
            [
                '2026-11-06' => 0,
                '2026-11-07' => 1,
            ],
            '2026-11-06 08:00:00'
        );

        $this->assertSame([
            [
                'student_id'      => 1,
                'attendance_date' => '2026-11-06',
                'status'          => 'Hadir',
                'updated_at'      => '2026-11-06 08:00:00',
            ],
        ], $prepared['records']);
        $this->assertContains('Status kehadiran tidak valid.', $prepared['errors']);
        $this->assertContains('Tanggal presensi di luar rentang tujuh hari aktif.', $prepared['errors']);
        $this->assertContains('Murid tidak ditemukan.', $prepared['errors']);
    }
}

final class TestableAttendanceController extends AttendanceController
{
    /**
     * @return list<string>
     */
    public function dateRange(\DateTimeImmutable $today): array
    {
        return $this->buildDateRange($today);
    }

    /**
     * @param array<string, string> $statuses
     */
    public function presentTotal(array $statuses): int
    {
        return $this->countPresentStatuses($statuses);
    }

    /**
     * @param array<mixed>       $submittedAttendance
     * @param array<int, int>    $validStudentIds
     * @param array<string, int> $validDates
     * @return array{records: list<array<string, int|string>>, errors: list<string>}
     */
    public function prepareRecords(
        array $submittedAttendance,
        array $validStudentIds,
        array $validDates,
        string $updatedAt
    ): array {
        return $this->prepareAttendanceRecords(
            $submittedAttendance,
            $validStudentIds,
            $validDates,
            $updatedAt
        );
    }
}
