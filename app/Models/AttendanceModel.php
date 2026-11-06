<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendances';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = ['student_id', 'attendance_date', 'status', 'updated_at'];

    protected $validationRules = [
        'student_id'      => 'required|is_natural_no_zero',
        'attendance_date' => 'required|valid_date[Y-m-d]',
        'status'          => 'required|in_list[Hadir,Sakit,Izin,Alpa]',
    ];

    protected $validationMessages = [
        'status' => [
            'required' => 'Status kehadiran wajib dipilih.',
            'in_list'  => 'Status kehadiran harus Hadir, Sakit, Izin, atau Alpa.',
        ],
    ];
}
