<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presensi Mingguan Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-Hadir { border-color: #198754; background-color: #d1e7dd; }
        .status-Sakit { border-color: #ffc107; background-color: #fff3cd; }
        .status-Izin { border-color: #0dcaf0; background-color: #cff4fc; }
        .status-Alpa { border-color: #dc3545; background-color: #f8d7da; }
    </style>
</head>
<body class="bg-light">
    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h2 mb-1">Presensi Mingguan Guru</h1>
            <p class="text-muted mb-0">
                Periode <?= esc($dates[0]['label']) ?> sampai <?= esc($dates[count($dates) - 1]['label']) ?>
            </p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('presensi/save') ?>" method="post">
            <?= csrf_field() ?>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-bordered table-hover align-middle mb-0 bg-white">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Nama Siswa</th>
                            <?php foreach ($dates as $date): ?>
                                <th scope="col" class="text-center text-nowrap"><?= esc($date['label']) ?></th>
                            <?php endforeach; ?>
                            <th scope="col" class="text-center text-nowrap">Total Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <?php $studentId = (int) $student['id']; ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <th scope="row" class="text-nowrap"><?= esc($student['name']) ?></th>
                                <?php foreach ($dates as $date): ?>
                                    <?php
                                    $value = old(
                                        'attendance.' . $studentId . '.' . $date['value'],
                                        $matrix[$studentId][$date['value']] ?? 'Hadir'
                                    );
                                    ?>
                                    <td>
                                        <label class="visually-hidden" for="attendance-<?= $studentId ?>-<?= esc($date['value']) ?>">
                                            Status presensi <?= esc($student['name']) ?> pada <?= esc($date['label']) ?>
                                        </label>
                                        <select
                                            id="attendance-<?= $studentId ?>-<?= esc($date['value']) ?>"
                                            name="attendance[<?= $studentId ?>][<?= esc($date['value']) ?>]"
                                            class="form-select form-select-sm status-select status-<?= esc($value) ?>"
                                        >
                                            <?php foreach ($statuses as $status): ?>
                                                <option value="<?= esc($status) ?>" <?= $value === $status ? 'selected' : '' ?>>
                                                    <?= esc($status) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                                <td class="total-hadir text-center fw-semibold"><?= $totals[$studentId] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Simpan Presensi</button>
            </div>
        </form>
    </main>

    <script>
        document.querySelectorAll('.status-select').forEach((select) => {
            select.addEventListener('change', () => {
                select.className = 'form-select form-select-sm status-select status-' + select.value;
            });
        });
    </script>
</body>
</html>
