<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class AttendanceModel extends Model
{
    protected $table = 'attendances';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = ['student_id', 'attendance_date', 'status', 'updated_at'];

    /**
     * Mengambil status presensi dan mengindeksnya berdasarkan murid serta tanggal.
     *
     * @param list<int> $studentIds
     * @return array<int, array<string, string>>
     */
    public function getStatusMatrix(array $studentIds, string $startDate, string $endDate): array
    {
        if ($studentIds === []) {
            return [];
        }

        $rows = $this->builder()
            ->select('student_id, attendance_date, status')
            ->whereIn('student_id', $studentIds)
            ->where('attendance_date >=', $startDate)
            ->where('attendance_date <=', $endDate)
            ->get()
            ->getResultArray();

        $matrix = [];

        foreach ($rows as $row) {
            $matrix[(int) $row['student_id']][$row['attendance_date']] = $row['status'];
        }

        return $matrix;
    }

    /**
     * Menyimpan seluruh matriks dengan memperbarui baris yang telah ada.
     *
     * @param list<array{student_id: int, attendance_date: string, status: string}> $records
     */
    public function upsertBatch(array $records): void
    {
        $database = db_connect();
        $database->transStart();
        $now = date('Y-m-d H:i:s');

        foreach ($records as $record) {
            $builder = $database->table($this->table);
            $existing = $builder
                ->select('id')
                ->where('student_id', $record['student_id'])
                ->where('attendance_date', $record['attendance_date'])
                ->get()
                ->getRowArray();

            $data = [
                'student_id' => $record['student_id'],
                'attendance_date' => $record['attendance_date'],
                'status' => $record['status'],
                'updated_at' => $now,
            ];

            if ($existing !== null) {
                $database->table($this->table)
                    ->where('id', $existing['id'])
                    ->update($data);

                continue;
            }

            $database->table($this->table)->insert($data);
        }

        $database->transComplete();

        if ($database->transStatus() === false) {
            throw new RuntimeException('Data presensi tidak dapat disimpan.');
        }
    }
}
