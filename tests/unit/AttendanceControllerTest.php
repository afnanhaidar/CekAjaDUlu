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
}
