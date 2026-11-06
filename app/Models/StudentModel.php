<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = ['name', 'created_at'];

    protected $validationRules = [
        'name' => 'required|max_length[100]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama murid wajib diisi.',
            'max_length' => 'Nama murid maksimal 100 karakter.',
        ],
    ];
}
