<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = ['name', 'created_at'];

    /**
     * Mengambil lima murid untuk ditampilkan pada matriks presensi.
     *
     * @return list<array<string, mixed>>
     */
    public function getWeeklyStudents(): array
    {
        return $this->orderBy('id', 'ASC')->findAll(5);
    }
}
