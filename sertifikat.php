<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);

// Get tes yang sudah selesai
$stmt = $conn->prepare("
    SELECT p.*, jt.nama_tes, jt.kode_tes, jt.sertifikat, jd.tanggal_tes 
    FROM pendaftaran p 
    JOIN jenis_tes jt ON p.jenis_tes_id = jt.id 
    LEFT JOIN jadwal_tes jd ON p.jadwal_tes_id = jd.id 
    WHERE p.nim = ? AND p.status = 'selesai' AND p.skor_tes IS NOT NULL
    ORDER BY jd.tanggal_tes DESC
");
$stmt->bind_param("s", $mahasiswa['nim']);
$stmt->execute();
$hasil_tes = $stmt->get_result();

$has_eligible_test = false;
$temp_results = [];
while ($row = $hasil_tes->fetch_assoc()) {
    $temp_results[] = $row;
    if ($row['sertifikat'] == 1) {
        if (!($mahasiswa['status'] == 'S1' && $row['skor_tes'] < 425)) {
            $has_eligible_test = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Sertifikat - TUTEP UPT Bahasa UNTAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <style>
        .navbar-gradient {background: linear-gradient(135deg, #2C3E50 0%,  #8BC34A 100%)}
        .bg-grey {background-color: #2C3E50 !important;}
        .text-grey {color: #2C3E50 !important;}
        .border-grey { border-color: #2C3E50 !important; }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-gradient">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="gambar/logouptbahasa.png" alt="Logo" height="30" class="me-2">
                TUTEP UPT Bahasa UNTAN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($mahasiswa['nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="bi bi-award me-2"></i>Sertifikat</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sertifikat</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Pesan Sertifikat Cetak -->
        <div class="row mb-4">
            <div class="col">
                <div class="card border-grey">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-2">
                                    <i class="bi bi-printer text-grey me-2"></i>Pesan Sertifikat Cetak
                                </h5>
                                <p class="text-muted mb-0">Pesan sertifikat cetak resmi untuk hasil tes Anda dengan biaya Rp 25.000</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <button class="btn btn-primary btn-lg <?php echo !$has_eligible_test ? 'disabled' : ''; ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#pesanSertifikatModal"
                                        <?php echo !$has_eligible_test ? 'disabled' : ''; ?>>
                                    <i class="bi bi-printer me-2"></i>Pesan Sekarang
                                </button>
                            </div>
                        </div>
                        <?php if (!$has_eligible_test && count($temp_results) > 0): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Tidak ada hasil tes yang memenuhi syarat untuk memesan sertifikat cetak.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pesanan Sertifikat -->
        <?php
        // Get riwayat pesanan sertifikat
        $stmt_pesanan = $conn->prepare("
            SELECT ps.*, jt.nama_tes, p.skor_tes, jt.nama_tes as jenis_tes_nama 
            FROM pemesanan_sertifikat ps
            LEFT JOIN pendaftaran p ON ps.pendaftaran_id = p.id
            LEFT JOIN jenis_tes jt ON p.jenis_tes_id = jt.id
            WHERE ps.nim = ?
            ORDER BY ps.tanggal_pesan DESC
        ");
        $stmt_pesanan->bind_param("s", $mahasiswa['nim']);
        $stmt_pesanan->execute();
        $riwayat_pesanan = $stmt_pesanan->get_result();
        ?>

        <?php if ($riwayat_pesanan->num_rows > 0): ?>
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <h5><i class="bi bi-clock-history me-2"></i>Riwayat Pesanan Sertifikat</h5>
            </div>
            <div class="col-12">
                <div class="card border-grey">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jenis Tes</th>
                                        <th>Skor</th>
                                        <th>Tanggal Pesan</th>
                                        <th>Biaya</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $status_badges = [
                                        'menunggu_validasi' => ['class' => 'warning', 'text' => 'Menunggu Validasi', 'icon' => 'clock-history'],
                                        'menunggu_pembayaran' => ['class' => 'primary', 'text' => 'Menunggu Pembayaran', 'icon' => 'credit-card'],
                                        'dibayar' => ['class' => 'success', 'text' => 'Dibayar', 'icon' => 'check-circle'],
                                        'diproses' => ['class' => 'info', 'text' => 'Diproses', 'icon' => 'printer'],
                                        'siap_diambil' => ['class' => 'success', 'text' => 'Siap Diambil', 'icon' => 'box-seam'],
                                        'selesai' => ['class' => 'secondary', 'text' => 'Selesai', 'icon' => 'check-all'],
                                        'dibatalkan' => ['class' => 'danger', 'text' => 'Dibatalkan', 'icon' => 'x-circle']
                                    ];
                                    
                                    while ($pesanan = $riwayat_pesanan->fetch_assoc()): 
                                        $status = $status_badges[$pesanan['status']] ?? ['class' => 'secondary', 'text' => 'Unknown', 'icon' => 'question'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pesanan['jenis_tes_nama']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $pesanan['skor_tes']; ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])); ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-success"><?php echo formatRupiah($pesanan['biaya']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $status['class']; ?>">
                                                <i class="bi bi-<?php echo $status['icon']; ?> me-1"></i>
                                                <?php echo $status['text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="detail_pesan_sertifikat.php?id=<?php echo $pesanan['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Hasil Tes & Sertifikat -->
        <?php if (count($temp_results) > 0): ?>
        <div class="row">
            <div class="col-12 mb-3">
                <h5><i class="bi bi-card-checklist me-2"></i>Hasil Tes Anda</h5>
            </div>
            <?php foreach ($temp_results as $tes): ?>
            <div class="col-md-6 mb-4">
                <div class="card border-grey h-100">
                    <div class="card-header bg-grey text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?php echo htmlspecialchars($tes['nama_tes']); ?></h6>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-check-circle me-1"></i>Selesai
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Skor -->
                        <div class="text-center py-4 mb-3 bg-light rounded">
                            <h1 class="display-3 text-primary mb-0"><?php echo $tes['skor_tes']; ?></h1>
                            <p class="text-muted mb-0">Skor Anda</p>
                        </div>

                        <!-- Info Tes -->
                        <div class="mb-3">
                            <small class="text-muted">Kode Tes:</small>
                            <p class="mb-2">
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($tes['kode_tes']); ?></span>
                                <?php if ($tes['sertifikat'] == 1): ?>
                                    <span class="badge bg-success ms-1">
                                        <i class="bi bi-award-fill me-1"></i>Dengan Sertifikat
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-1">Tanpa Sertifikat</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Tanggal Tes:</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-calendar-event text-primary"></i> 
                                    <?php echo !empty($tes['tanggal_tes']) ? formatTanggal($tes['tanggal_tes']) : '-'; ?>
                                </strong>
                            </p>
                        </div>

                        <!-- Status Sertifikat -->
                        <div class="d-grid">
                            <?php 
                            if ($tes['sertifikat'] == 1):
                                $dapat_sertifikat = true;
                                if ($mahasiswa['status'] == 'S1' && $tes['skor_tes'] < 425) {
                                    $dapat_sertifikat = false;
                                }
                                
                                if ($dapat_sertifikat): 
                            ?>
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <strong>Memenuhi syarat untuk sertifikat cetak</strong>
                                    <br><small>Klik tombol "Pesan Sekarang" di atas untuk memesan.</small>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Skor minimal 425</strong> untuk mendapatkan sertifikat mahasiswa S1
                                </div>
                            <?php 
                                endif;
                            else: 
                            ?>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Tes ini <strong>tidak termasuk sertifikat</strong>. Sertifikat hanya untuk paket tes tertentu.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="mt-3 mb-2">Belum Ada Hasil Tes</h5>
                        <p class="text-muted mb-4">Anda belum memiliki hasil tes yang dapat ditampilkan</p>
                        <a href="daftar_tes.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Daftar Tes Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Pesan Sertifikat Cetak -->
    <div class="modal fade" id="pesanSertifikatModal" tabindex="-1" aria-labelledby="pesanSertifikatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-grey text-white">
                    <h5 class="modal-title" id="pesanSertifikatModalLabel">
                        <i class="bi bi-printer me-2"></i>Pesan Sertifikat Cetak
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_pesan_sertifikat.php" method="POST">
                    <div class="modal-body">
                        <!-- Informasi Biaya -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informasi Penting</h6>
                            <hr>
                            <ul class="mb-0 ps-3">
                                <li>Biaya cetak sertifikat: <strong>Rp 25.000</strong></li>
                                <li>Pembayaran via <strong>Transfer Bank BNI</strong></li>
                                <li>Sertifikat dapat diambil <strong>3-5 hari kerja</strong> setelah pembayaran dikonfirmasi</li>
                                <li>Pengambilan di <strong>UPT Bahasa UNTAN</strong></li>
                            </ul>
                        </div>

                        <!-- Form Fields -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <input type="text" class="form-control" value="Mahasiswa <?php echo htmlspecialchars($mahasiswa['status']); ?> UNTAN" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Fakultas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="fakultas" value="<?php echo htmlspecialchars($mahasiswa['fakultas']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Identitas (NIK) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_identitas" placeholder="Masukkan NIK (16 digit)" pattern="[0-9]{16}" required>
                            <small class="form-text text-muted">Sesuai KTP</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_lahir" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">No. HP/WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_hp" placeholder="08xxxxxxxxxx" pattern="08[0-9]{8,11}" required>
                            <small class="form-text text-muted">Untuk konfirmasi pengambilan sertifikat</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Hasil Tes <span class="text-danger">*</span></label>
                            <select class="form-select" name="pendaftaran_id" required>
                                <option value="">-- Pilih Tes --</option>
                                <?php 
                                foreach ($temp_results as $tes): 
                                    if ($tes['sertifikat'] == 1):
                                        $dapat_sertifikat = true;
                                        if ($mahasiswa['status'] == 'S1' && $tes['skor_tes'] < 425) {
                                            $dapat_sertifikat = false;
                                        }
                                        
                                        if ($dapat_sertifikat):
                                ?>
                                    <option value="<?php echo $tes['id']; ?>">
                                        <?php 
                                        echo htmlspecialchars($tes['nama_tes']) . ' - '; 
                                        echo (!empty($tes['tanggal_tes']) ? formatTanggal($tes['tanggal_tes']) : 'Tanggal belum tersedia');
                                        echo ' (Skor: ' . $tes['skor_tes'] . ')'; 
                                        ?>
                                    </option>
                                <?php 
                                        endif;
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                            <small class="form-text text-muted">Hanya tes dengan paket sertifikat yang dapat dipesan</small>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Catatan:</strong> Setelah submit, Anda akan mendapatkan informasi pembayaran dan diminta upload bukti transfer.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Submit Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="text-center text-muted">
                <small>&copy; <?php echo date('Y'); ?> UPT Bahasa UNTAN. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>