<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AttendanceViewTest extends CIUnitTestCase
{
    public function testRendersSixItbLinkWithSafeNewTabAttributes(): void
    {
        $output = view('attendance_view', [
            'students'              => [],
            'dates'                 => [
                '2026-11-06',
                '2026-11-07',
                '2026-11-08',
                '2026-11-09',
                '2026-11-10',
                '2026-11-11',
                '2026-11-12',
            ],
            'attendanceByStudent'   => [],
            'totalPresentByStudent' => [],
            'sixItbUrl'             => 'http://six.itb.ac.id/',
        ]);

        $this->assertStringContainsString('Kunjungi SIX ITB', $output);
        $this->assertStringContainsString('href="http://six.itb.ac.id/"', $output);
        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertStringContainsString('rel="noopener noreferrer"', $output);
    }
}
