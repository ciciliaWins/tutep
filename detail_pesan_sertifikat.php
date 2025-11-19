<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);
$pesanan_id = $_GET['id'] ?? 0;

// Get pesanan detail
$stmt = $conn->prepare("
    SELECT ps.*, jt.nama_tes, p.skor_tes, jt.nama_tes as jenis_tes_nama, jd.tanggal_tes
    FROM pemesanan_sertifikat ps
    LEFT JOIN pendaftaran p ON ps.pendaftaran_id = p.id
    LEFT JOIN jenis_tes jt ON p.jenis_tes_id = jt.id
    LEFT JOIN jadwal_tes jd ON p.jadwal_tes_id = jd.id
    WHERE ps.id = ? AND ps.nim = ?
");
$stmt->bind_param("is", $pesanan_id, $mahasiswa['nim']);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();

if (!$pesanan) {
    header('Location: sertifikat.php');
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $upload_dir = 'uploads/bukti_pembayaran_sertifikat/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['bukti_pembayaran']['name']);
    $target_file = $upload_dir . $file_name;
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($_FILES['bukti_pembayaran']['type'], $allowed_types)) {
        $error = 'Format file tidak valid! Hanya JPG, PNG, atau PDF yang diperbolehkan.';
    } elseif ($_FILES['bukti_pembayaran']['size'] > $max_size) {
        $error = 'Ukuran file terlalu besar! Maksimal 2MB.';
    } elseif (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("UPDATE pemesanan_sertifikat SET bukti_pembayaran = ?, status = 'divalidasi' WHERE id = ?");
        $stmt->bind_param("si", $file_name, $pesanan_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Bukti pembayaran berhasil diupload! Menunggu validasi admin.';
            header('Location: detail_pesan_sertifikat.php?id=' . $pesanan_id);
            exit();
        } else {
            $error = 'Gagal menyimpan data. Silakan coba lagi.';
        }
    } else {
        $error = 'Gagal mengupload file. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Detail Pesanan Sertifikat - TUTEP UPT Bahasa UNTAN</title>
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
                <h2><i class="bi bi-printer me-2"></i>Detail Pesanan Sertifikat</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="sertifikat.php">Sertifikat</a></li>
                        <li class="breadcrumb-item active">Detail Pesanan</li>
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Status Progress -->
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Status Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item <?php echo in_array($pesanan['status'], ['menunggu_validasi', 'divalidasi', 'dibayar', 'diproses', 'siap_diambil', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-1-circle-fill me-2"></i>Pesanan Diterima</h6>
                                    <small><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])); ?></small>
                                </div>
                                <small class="text-muted">Pesanan Anda telah diterima sistem</small>
                            </div>
                            
                            <div class="list-group-item <?php echo in_array($pesanan['status'], ['divalidasi', 'dibayar', 'diproses', 'siap_diambil', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-2-circle-fill me-2"></i>Menunggu Pembayaran</h6>
                                    <?php if (!empty($pesanan['tanggal_pembayaran']) && $pesanan['status'] != 'menunggu_validasi'): ?>
                                        <small><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pembayaran'])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php 
                                    if (in_array($pesanan['status'], ['dibayar', 'diproses', 'siap_diambil', 'selesai'])) {
                                        echo 'Pembayaran telah dikonfirmasi';
                                    } elseif ($pesanan['status'] == 'divalidasi') {
                                        echo 'Bukti pembayaran sedang divalidasi admin...';
                                    } else {
                                        echo 'Menunggu upload bukti pembayaran...';
                                    }
                                    ?>
                                </small>
                            </div>
                            
                            <div class="list-group-item <?php echo in_array($pesanan['status'], ['diproses', 'siap_diambil', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-3-circle-fill me-2"></i>Sedang Diproses</h6>
                                </div>
                                <small class="text-muted">
                                    <?php echo in_array($pesanan['status'], ['diproses', 'siap_diambil', 'selesai']) ? 'Sertifikat sedang dicetak' : 'Menunggu proses cetak...'; ?>
                                </small>
                            </div>
                            
                            <div class="list-group-item <?php echo in_array($pesanan['status'], ['siap_diambil', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-4-circle-fill me-2"></i>Siap Diambil</h6>
                                </div>
                                <small class="text-muted">
                                    <?php echo in_array($pesanan['status'], ['siap_diambil', 'selesai']) ? 'Sertifikat siap diambil di UPT Bahasa UNTAN' : 'Menunggu...'; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Pesanan -->
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detail Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Jenis Tes:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pesanan['jenis_tes_nama']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Skor:</div>
                            <div class="col-sm-8"><h4 class="text-primary mb-0"><?php echo $pesanan['skor_tes']; ?></h4></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Tanggal Tes:</div>
                            <div class="col-sm-8"><?php echo !empty($pesanan['tanggal_tes']) ? formatTanggal($pesanan['tanggal_tes']) : '-'; ?></div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nama:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pesanan['nama']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">NIK:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pesanan['no_identitas']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Tanggal Lahir:</div>
                            <div class="col-sm-8"><?php echo formatTanggal($pesanan['tanggal_lahir']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Fakultas:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pesanan['fakultas']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">No. HP:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pesanan['no_hp']); ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Biaya Cetak:</div>
                            <div class="col-sm-8"><h4 class="text-success mb-0"><?php echo formatRupiah($pesanan['biaya']); ?></h4></div>
                        </div>
                    </div>
                </div>

                <!-- Pembayaran Section -->
                <?php if ($pesanan['status'] == 'divalidasi' && empty($pesanan['bukti_pembayaran'])): ?>
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-bank me-2"></i>Informasi Rekening</h6>
                            <hr>
                            <p class="mb-2"><strong>Bank:</strong> BNI</p>
                            <p class="mb-2"><strong>No. Rekening:</strong> 1234567890</p>
                            <p class="mb-2"><strong>Atas Nama:</strong> UPT Bahasa UNTAN</p>
                            <p class="mb-0"><strong>Jumlah Transfer:</strong> <span class="text-danger"><?php echo formatRupiah($pesanan['biaya']); ?></span></p>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Bukti Pembayaran <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*,.pdf" required>
                                <small class="form-text text-muted">Format: JPG, PNG, PDF | Maksimal: 2MB</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-2"></i>Upload Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bukti Pembayaran -->
                <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
                <div class="card <?php echo in_array($pesanan['status'], ['dibayar', 'diproses', 'siap_diambil', 'selesai']) ? 'border-success' : 'border-primary'; ?> mb-4">
                    <div class="card-header <?php echo in_array($pesanan['status'], ['dibayar', 'diproses', 'siap_diambil', 'selesai']) ? 'bg-success' : 'bg-primary'; ?> text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if (in_array($pesanan['status'], ['dibayar', 'diproses', 'siap_diambil', 'selesai'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Pembayaran telah dikonfirmasi!</strong>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-clock-history me-2"></i>
                                <strong>Menunggu validasi admin</strong>
                            </div>
                        <?php endif; ?>
                        
                        <h6 class="fw-bold mb-3">Preview Bukti Transfer:</h6>
                        <?php 
                        $file_ext = strtolower(pathinfo($pesanan['bukti_pembayaran'], PATHINFO_EXTENSION));
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): 
                        ?>
                            <img src="uploads/bukti_pembayaran_sertifikat/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" 
                                 class="img-fluid rounded border" alt="Bukti Pembayaran">
                        <?php elseif ($file_ext == 'pdf'): ?>
                            <a href="uploads/bukti_pembayaran_sertifikat/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-file-pdf me-2"></i>Lihat Bukti Pembayaran (PDF)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status Saat Ini</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $status_info = [
                            'menunggu_validasi' => ['class' => 'warning', 'text' => 'Menunggu Validasi', 'icon' => 'clock-history'],
                            'divalidasi' => ['class' => 'primary', 'text' => 'Menunggu Pembayaran', 'icon' => 'credit-card'],
                            'dibayar' => ['class' => 'success', 'text' => 'Pembayaran Dikonfirmasi', 'icon' => 'check-circle'],
                            'diproses' => ['class' => 'info', 'text' => 'Sedang Diproses', 'icon' => 'printer'],
                            'siap_diambil' => ['class' => 'success', 'text' => 'Siap Diambil', 'icon' => 'box-seam'],
                            'selesai' => ['class' => 'secondary', 'text' => 'Selesai', 'icon' => 'check-all'],
                        ];
                        $current_status = $status_info[$pesanan['status']] ?? ['class' => 'secondary', 'text' => 'Unknown', 'icon' => 'question'];
                        ?>
                        <div class="alert alert-<?php echo $current_status['class']; ?> mb-3">
                            <i class="bi bi-<?php echo $current_status['icon']; ?> me-2"></i>
                            <strong><?php echo $current_status['text']; ?></strong>
                        </div>
                        
                        <?php if ($pesanan['status'] == 'siap_diambil'): ?>
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading"><i class="bi bi-geo-alt me-2"></i>Pengambilan Sertifikat</h6>
                                <hr>
                                <p class="mb-2"><strong>Lokasi:</strong> UPT Bahasa UNTAN</p>
                                <p class="mb-2"><strong>Hari:</strong> Senin - Jumat</p>
                                <p class="mb-0"><strong>Jam:</strong> 08:00 - 15:00 WIB</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Butuh Bantuan?</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Jika ada pertanyaan, hubungi kami:</p>
                        <div class="mb-2">
                            <i class="bi bi-whatsapp text-success me-2"></i>
                            <strong>0812-3456-7890</strong>
                        </div>
                        <div class="mb-0">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <strong>uptbahasa@untan.ac.id</strong>
                        </div>
                    </div>
                </div>
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