<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $createdAt = date('Y-m-d H:i:s');
        $students = [];

        for ($number = 1; $number <= 5; $number++) {
            $students[] = [
                'name'       => "Murid {$number}",
                'created_at' => $createdAt,
            ];
        }

        $this->db->table('students')->insertBatch($students);
    }
}
