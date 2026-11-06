<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\RedirectResponse;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

class AttendanceController extends BaseController
{
    private const UPLOAD_DIRECTORY = 'uploads/surat/';

    /**
     * Menampilkan matriks presensi untuk tujuh hari aktif.
     *
     * @return string
     */
    public function index(): string
    {
        $dates = $this->buildDateRange($this->todayInJakarta());
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();
        $students = $studentModel->orderBy('id', 'ASC')->findAll(5);
        $attendances = $attendanceModel
            ->whereIn('attendance_date', $dates)
            ->findAll();

        $attendanceByStudent = [];

        foreach ($attendances as $attendance) {
            $studentId = (int) $attendance['student_id'];
            $attendanceByStudent[$studentId][$attendance['attendance_date']] = $attendance['status'];
        }

        $totalPresentByStudent = [];

        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $totalPresentByStudent[$studentId] = $this->countPresentStatuses(
                $attendanceByStudent[$studentId] ?? []
            );
        }

        return view('attendance_view', [
            'students'              => $students,
            'dates'                 => $dates,
            'attendanceByStudent'   => $attendanceByStudent,
            'totalPresentByStudent' => $totalPresentByStudent,
        ]);
    }

    /**
     * Menyimpan seluruh perubahan presensi dalam satu transaksi.
     */
    public function save()
    {
        $dates = $this->buildDateRange($this->todayInJakarta());
        $validDates = array_flip($dates);
        $submittedAttendance = $this->request->getPost('attendance');

        if (! is_array($submittedAttendance)) {
            return redirect()->to('/presensi')->with('error', 'Data presensi tidak valid.');
        }

        $students = (new StudentModel())->orderBy('id', 'ASC')->findAll(5);
        $validStudentIds = array_flip(array_map(
            static fn (array $student): int => (int) $student['id'],
            $students
        ));
        $preparedAttendance = $this->prepareAttendanceRecords(
            $submittedAttendance,
            $validStudentIds,
            $validDates,
            $this->nowInJakarta()->format('Y-m-d H:i:s')
        );
        $attendances = $preparedAttendance['records'];
        $validationErrors = array_values(array_unique($preparedAttendance['errors']));

        $database = db_connect();
        $database->transStart();

        try {
            if (! (new AttendanceModel())->upsertBatch($attendances)) {
                throw new RuntimeException('Penyimpanan presensi gagal.');
            }
        } catch (Throwable) {
            $database->transRollback();

            return redirect()->to('/presensi')->with('error', 'Presensi gagal disimpan.');
        }

        $database->transComplete();

        if ($database->transStatus() === false) {
            return redirect()->to('/presensi')->with('error', 'Presensi gagal disimpan.');
        }

        $redirect = redirect()->to('/presensi');

        if ($attendances !== [] || $validationErrors === []) {
            $redirect = $redirect->with('success', 'Presensi berhasil disimpan!');
        }

        if ($validationErrors !== []) {
            return $redirect->with('warning', implode(' ', $validationErrors));
        }

        return $redirect;
    }

    /**
     * Mengunggah foto surat ke penyimpanan di luar folder public.
     */
    public function upload(): RedirectResponse
    {
        $validationError = $this->photoUploadValidationError();

        if ($validationError !== null) {
            return redirect()->to('/presensi')->with('error', $validationError);
        }

        $file = $this->uploadedPhoto();

        if ($file === null || ! $file->isValid()) {
            return redirect()->to('/presensi')->with('error', 'Foto surat tidak valid.');
        }

        try {
            $this->storeUploadedPhoto($file);
        } catch (Throwable) {
            return redirect()->to('/presensi')->with('error', 'Foto surat gagal diunggah.');
        }

        return redirect()->to('/presensi')->with('success', 'Foto berhasil diunggah');
    }

    /**
     * @return list<string>
     */
    protected function buildDateRange(DateTimeImmutable $today): array
    {
        $dates = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $dates[] = $today->modify("+{$offset} days")->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * @param array<string, string> $statuses
     */
    protected function countPresentStatuses(array $statuses): int
    {
        return count(array_filter(
            $statuses,
            static fn (string $status): bool => $status === 'Hadir'
        ));
    }

    /**
     * Memisahkan data presensi yang dapat disimpan dari data yang tidak valid.
     *
     * @param array<mixed>              $submittedAttendance
     * @param array<int, int>           $validStudentIds
     * @param array<string, int>        $validDates
     * @return array{records: list<array<string, int|string>>, errors: list<string>}
     */
    protected function prepareAttendanceRecords(
        array $submittedAttendance,
        array $validStudentIds,
        array $validDates,
        string $updatedAt
    ): array {
        $validStatuses = ['Hadir', 'Sakit', 'Izin', 'Alpa'];
        $records = [];
        $errors = [];

        foreach ($submittedAttendance as $studentId => $dailyAttendance) {
            $studentId = filter_var($studentId, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if ($studentId === false || ! isset($validStudentIds[$studentId])) {
                $errors[] = 'Murid tidak ditemukan.';

                continue;
            }

            if (! is_array($dailyAttendance)) {
                $errors[] = 'Data presensi tidak valid.';

                continue;
            }

            foreach ($dailyAttendance as $date => $status) {
                if ($status === '') {
                    continue;
                }

                if (! is_string($date) || ! isset($validDates[$date])) {
                    $errors[] = 'Tanggal presensi di luar rentang tujuh hari aktif.';

                    continue;
                }

                if (! is_string($status) || ! in_array($status, $validStatuses, true)) {
                    $errors[] = 'Status kehadiran tidak valid.';

                    continue;
                }

                $records[] = [
                    'student_id'      => $studentId,
                    'attendance_date' => $date,
                    'status'          => $status,
                    'updated_at'      => $updatedAt,
                ];
            }
        }

        return [
            'records' => $records,
            'errors'  => $errors,
        ];
    }

    protected function todayInJakarta(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
    }

    protected function nowInJakarta(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    }

    /**
     * @return array<string, array{label: string, rules: list<string>, errors: array<string, string>}>
     */
    protected function photoValidationRules(): array
    {
        return [
            'surat' => [
                'label' => 'Foto Surat',
                'rules' => [
                    'uploaded[surat]',
                    'is_image[surat]',
                    'mime_in[surat,image/jpg,image/jpeg,image/png]',
                    'max_size[surat,2048]',
                ],
                'errors' => [
                    'uploaded'  => 'Foto surat wajib dipilih.',
                    'is_image'  => 'Foto surat harus berupa gambar.',
                    'mime_in'   => 'Foto surat harus berformat JPEG atau PNG.',
                    'max_size'  => 'Ukuran foto surat maksimal 2 MB.',
                ],
            ],
        ];
    }

    protected function photoUploadValidationError(): ?string
    {
        if ($this->validateData([], $this->photoValidationRules())) {
            return null;
        }

        return $this->validator->getError('surat') ?? 'Foto surat tidak valid.';
    }

    protected function uploadedPhoto(): ?UploadedFile
    {
        return $this->request->getFile('surat');
    }

    protected function storeUploadedPhoto(UploadedFile $file): void
    {
        $file->move($this->uploadDirectoryPath(), $file->getRandomName());
    }

    protected function uploadDirectoryPath(): string
    {
        $writablePath = defined('WRITABLEPATH') ? WRITABLEPATH : WRITEPATH;

        return $writablePath . self::UPLOAD_DIRECTORY;
    }
}
