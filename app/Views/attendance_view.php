<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presensi Mingguan Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <main class="container py-4">
        <?php
        $statusClasses = [
            ''      => 'bg-secondary-subtle text-secondary-emphasis',
            'Hadir' => 'bg-success-subtle text-success-emphasis',
            'Sakit' => 'bg-warning-subtle text-warning-emphasis',
            'Izin'  => 'bg-info-subtle text-info-emphasis',
            'Alpa'  => 'bg-danger-subtle text-danger-emphasis',
        ];
        ?>
        <h1 class="h2 mb-1">Presensi Mingguan Guru</h1>
        <p class="text-muted mb-4">
            Rentang tanggal aktif: <?= esc($dates[0]) ?> sampai <?= esc($dates[6]) ?>
        </p>

        <?php if (session()->getFlashdata('success') !== null): ?>
            <div class="alert alert-success" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error') !== null): ?>
            <div class="alert alert-danger" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning') !== null): ?>
            <div class="alert alert-warning" role="alert">
                <?= esc(session()->getFlashdata('warning')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= esc(site_url('presensi/save')) ?>" method="post">
            <?= csrf_field() ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle bg-white">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Siswa</th>
                            <?php foreach ($dates as $date): ?>
                                <th scope="col"><?= esc($date) ?></th>
                            <?php endforeach; ?>
                            <th scope="col">Total Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <?php $studentId = (int) $student['id']; ?>
                            <tr>
                                <td><?= esc((string) ($index + 1)) ?></td>
                                <td><?= esc($student['name']) ?></td>
                                <?php foreach ($dates as $date): ?>
                                    <?php $selectedStatus = $attendanceByStudent[$studentId][$date] ?? ''; ?>
                                    <td>
                                        <label class="visually-hidden" for="attendance-<?= esc((string) $studentId) ?>-<?= esc($date) ?>">
                                            <?= esc('Presensi ' . $student['name'] . ' pada ' . $date) ?>
                                        </label>
                                        <select
                                            class="form-select form-select-sm <?= esc($statusClasses[$selectedStatus] ?? $statusClasses['']) ?>"
                                            id="attendance-<?= esc((string) $studentId) ?>-<?= esc($date) ?>"
                                            name="<?= esc('attendance[' . $studentId . '][' . $date . ']') ?>"
                                        >
                                            <option value="" <?= $selectedStatus === '' ? 'selected' : '' ?>>Belum diisi</option>
                                            <?php foreach (['Hadir', 'Sakit', 'Izin', 'Alpa'] as $status): ?>
                                                <option value="<?= esc($status) ?>" <?= $selectedStatus === $status ? 'selected' : '' ?>>
                                                    <?= esc($status) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                                <td><?= esc((string) ($totalPresentByStudent[$studentId] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Presensi</button>
        </form>

        <section class="card mt-4">
            <div class="card-body">
                <h2 class="h5">Ambil Foto Surat</h2>
                <form id="photo-upload-form" action="<?= esc(site_url('presensi/upload')) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div id="camera-message" class="alert alert-warning d-none" role="alert"></div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" id="activate-camera" class="btn btn-outline-secondary">Aktifkan Kamera</button>
                        <button type="button" id="capture-photo" class="btn btn-primary" disabled>Ambil Foto</button>
                        <button type="button" id="retake-photo" class="btn btn-outline-secondary d-none">Ulangi</button>
                    </div>

                    <video id="camera-preview" class="w-100 rounded border mb-3" autoplay playsinline hidden></video>
                    <canvas id="photo-canvas" hidden></canvas>
                    <img id="photo-preview" class="img-fluid rounded border mb-3" alt="Pratinjau foto surat" hidden>

                    <div class="mb-3">
                        <label class="form-label" for="surat">Pilih Foto dari Perangkat</label>
                        <input
                            class="form-control"
                            type="file"
                            id="surat"
                            name="surat"
                            accept="image/jpeg,image/png"
                            capture="environment"
                            required
                        >
                    </div>
                    <button type="submit" id="upload-photo" class="btn btn-outline-secondary" disabled>Unggah Foto</button>
                </form>
            </div>
        </section>
    </main>
    <script src="<?= esc(base_url('js/camera.js')) ?>" defer></script>
</body>
</html>
