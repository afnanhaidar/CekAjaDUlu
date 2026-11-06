<?php

use App\Models\AttendanceModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AttendanceModelTest extends CIUnitTestCase
{
    public function testAcceptsEachValidAttendanceStatus(): void
    {
        $model = new AttendanceModel();

        foreach (['Hadir', 'Sakit', 'Izin', 'Alpa'] as $status) {
            $isValid = $model->validate([
                'student_id'      => 1,
                'attendance_date' => '2026-11-06',
                'status'          => $status,
            ]);

            $this->assertTrue($isValid, "Status {$status} seharusnya valid.");
        }
    }

    public function testRejectsAnInvalidAttendanceStatus(): void
    {
        $model = new AttendanceModel();

        $isValid = $model->validate([
            'student_id'      => 1,
            'attendance_date' => '2026-11-06',
            'status'          => 'Terlambat',
        ]);

        $this->assertFalse($isValid);
        $this->assertArrayHasKey('status', $model->errors());
    }
}
