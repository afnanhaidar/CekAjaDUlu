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

        $database = db_connect();
        $names = [];

        for ($number = 1; $number <= 5; $number++) {
            $names[] = [
                'name' => 'Murid Uji Presensi ' . $number,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $database->table('students')->insertBatch($names);
        $students = $database->table('students')
            ->select('id')
            ->like('name', 'Murid Uji Presensi ', 'after')
            ->orderBy('id', 'DESC')
            ->get(5)
            ->getResultArray();

        $this->studentIds = array_reverse(
            array_map(static fn (array $student): int => (int) $student['id'], $students)
        );
    }

    protected function tearDown(): void
    {
        $database = db_connect();

        if ($this->studentIds !== []) {
            $database->table('attendances')->whereIn('student_id', $this->studentIds)->delete();
            $database->table('students')->whereIn('id', $this->studentIds)->delete();
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
