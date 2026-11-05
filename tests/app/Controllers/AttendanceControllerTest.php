<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class AttendanceControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var list<int>
     */
    private array $studentIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $students = db_connect()
            ->table('students')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->get(5)
            ->getResultArray();

        $this->studentIds = array_map(
            static fn (array $student): int => (int) $student['id'],
            $students
        );

        $this->assertCount(5, $this->studentIds, 'Seed lima data murid harus tersedia untuk pengujian.');
        db_connect()->table('attendances')->whereIn('student_id', $this->studentIds)->delete();
    }

    protected function tearDown(): void
    {
        if ($this->studentIds !== []) {
            db_connect()->table('attendances')->whereIn('student_id', $this->studentIds)->delete();
        }

        parent::tearDown();
    }

    public function testBatchSaveMenyimpanDanMemperbaruiDataPresensi(): void
    {
        $dates = $this->weeklyDates();
        $payload = $this->payload($dates, 'Hadir');

        $response = $this->withBodyFormat('form')->post('/presensi/save', [
            'attendance' => $payload,
        ]);

        $response->assertRedirectTo('/presensi');
        $response->assertSessionHas('success', 'Presensi berhasil disimpan!');

        $database = db_connect();
        $this->assertSame(
            35,
            $database->table('attendances')->whereIn('student_id', $this->studentIds)->countAllResults()
        );

        $payload[$this->studentIds[0]][$dates[0]] = 'Alpa';
        $this->withBodyFormat('form')->post('/presensi/save', ['attendance' => $payload]);

        $this->assertSame(
            35,
            $database->table('attendances')->whereIn('student_id', $this->studentIds)->countAllResults()
        );
        $this->assertSame(
            'Alpa',
            $database->table('attendances')
                ->where('student_id', $this->studentIds[0])
                ->where('attendance_date', $dates[0])
                ->get()
                ->getRowArray()['status']
        );
    }

    public function testStatusTersimpanTampilSetelahHalamanDimuatUlang(): void
    {
        $dates = $this->weeklyDates();
        $payload = $this->payload($dates, 'Hadir');
        $payload[$this->studentIds[0]][$dates[0]] = 'Sakit';

        $this->withBodyFormat('form')->post('/presensi/save', ['attendance' => $payload]);
        $response = $this->get('/presensi');

        $response->assertOK();
        $response->assertSee(
            'name="attendance[' . $this->studentIds[0] . '][' . $dates[0] . ']"',
            false
        );
        $response->assertSee('value="Sakit" selected', false);
    }

    public function testTotalHadirDihitungDenganBenar(): void
    {
        $dates = $this->weeklyDates();
        $payload = $this->payload($dates, 'Alpa');

        foreach (array_slice($dates, 0, 3) as $date) {
            $payload[$this->studentIds[0]][$date] = 'Hadir';
        }

        $this->withBodyFormat('form')->post('/presensi/save', ['attendance' => $payload]);
        $response = $this->get('/presensi');

        $response->assertOK();
        $response->assertSee('<td class="total-hadir text-center fw-semibold">3</td>', false);
    }

    /**
     * @return list<string>
     */
    private function weeklyDates(): array
    {
        $dates = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $dates[] = date('Y-m-d', strtotime('+' . $offset . ' days'));
        }

        return $dates;
    }

    /**
     * @param list<string> $dates
     * @return array<int, array<string, string>>
     */
    private function payload(array $dates, string $status): array
    {
        $payload = [];

        foreach ($this->studentIds as $studentId) {
            foreach ($dates as $date) {
                $payload[$studentId][$date] = $status;
            }
        }

        return $payload;
    }
}
