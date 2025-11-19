<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);
$pendaftaran_id = $_GET['id'] ?? 0;

// Get pendaftaran detail - sesuaikan dengan struktur database
$stmt = $conn->prepare("
    SELECT p.*, jt.nama_tes, jt.kode_tes, jd.tanggal_tes, jd.waktu_tes, jd.ruangan 
    FROM pendaftaran p 
    JOIN jenis_tes jt ON p.jenis_tes_id = jt.id 
    LEFT JOIN jadwal_tes jd ON p.jadwal_tes_id = jd.id 
    WHERE p.id = ? AND p.nim = ?
");
$stmt->bind_param("is", $pendaftaran_id, $mahasiswa['nim']);
$stmt->execute();
$pendaftaran = $stmt->get_result()->fetch_assoc();

if (!$pendaftaran) {
    header('Location: dashboard.php');
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $upload_dir = 'uploads/bukti_pembayaran/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['bukti_pembayaran']['name']);
    $target_file = $upload_dir . $file_name;
    
    // Validasi file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($_FILES['bukti_pembayaran']['type'], $allowed_types)) {
        $error = 'Format file tidak valid! Hanya JPG, PNG, atau PDF yang diperbolehkan.';
    } elseif ($_FILES['bukti_pembayaran']['size'] > $max_size) {
        $error = 'Ukuran file terlalu besar! Maksimal 2MB.';
    } elseif (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("UPDATE pendaftaran SET bukti_pembayaran = ?, status = 'menunggu_pembayaran', tanggal_pembayaran = NOW() WHERE id = ?");
        $stmt->bind_param("si", $file_name, $pendaftaran_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Bukti pembayaran berhasil diupload! Menunggu validasi admin.';
            header('Location: detail_pendaftaran.php?id=' . $pendaftaran_id);
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
    <title>Detail Pendaftaran - TUTEP UPT Bahasa UNTAN</title>
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
                <h2><i class="bi bi-receipt me-2"></i>Detail Pendaftaran</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Pendaftaran</li>
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
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Status Pendaftaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <!-- Step 1: Pendaftaran Diterima -->
                            <div class="list-group-item <?php echo in_array($pendaftaran['status'], ['menunggu_validasi', 'divalidasi', 'menunggu_pembayaran', 'dibayar', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="bi bi-1-circle-fill me-2"></i>Pendaftaran Diterima
                                    </h6>
                                    <small><?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_daftar'])); ?></small>
                                </div>
                                <small class="text-muted">Pendaftaran Anda telah diterima sistem</small>
                            </div>
                            
                            <!-- Step 2: Divalidasi Admin -->
                            <div class="list-group-item <?php echo in_array($pendaftaran['status'], ['divalidasi', 'menunggu_pembayaran', 'dibayar', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="bi bi-2-circle-fill me-2"></i>Divalidasi Admin
                                    </h6>
                                    <?php if (!empty($pendaftaran['tanggal_validasi'])): ?>
                                        <small><?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_validasi'])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo !empty($pendaftaran['tanggal_validasi']) ? 'Pendaftaran telah divalidasi admin' : 'Menunggu validasi admin...'; ?>
                                </small>
                            </div>
                            
                            <!-- Step 3: Pembayaran Dikonfirmasi -->
                            <div class="list-group-item <?php echo in_array($pendaftaran['status'], ['dibayar', 'selesai']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="bi bi-3-circle-fill me-2"></i>Pembayaran Dikonfirmasi
                                    </h6>
                                    <?php if (!empty($pendaftaran['tanggal_pembayaran']) && in_array($pendaftaran['status'], ['dibayar', 'selesai'])): ?>
                                        <small><?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_pembayaran'])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php 
                                    if (in_array($pendaftaran['status'], ['dibayar', 'selesai'])) {
                                        echo 'Pembayaran telah dikonfirmasi oleh admin';
                                    } elseif ($pendaftaran['status'] == 'menunggu_pembayaran') {
                                        echo 'Bukti pembayaran sedang divalidasi admin...';
                                    } else {
                                        echo 'Menunggu pembayaran...';
                                    }
                                    ?>
                                </small>
                            </div>
                            
                            <!-- Step 4: Selesai -->
                            <div class="list-group-item <?php echo $pendaftaran['status'] == 'selesai' ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="bi bi-4-circle-fill me-2"></i>Selesai
                                    </h6>
                                </div>
                                <small class="text-muted">
                                    <?php echo $pendaftaran['status'] == 'selesai' ? 'Tes telah selesai dilaksanakan' : 'Menunggu pelaksanaan tes'; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Pendaftaran -->
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detail Tes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Jenis Tes:</div>
                            <div class="col-sm-8">
                                <?php echo htmlspecialchars($pendaftaran['nama_tes']); ?>
                                <span class="badge bg-info text-dark ms-2"><?php echo htmlspecialchars($pendaftaran['kode_tes']); ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nama Lengkap:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pendaftaran['nama']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">NIM:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pendaftaran['nim']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Email:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pendaftaran['email']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">No. HP:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($pendaftaran['no_hp']); ?></div>
                        </div>
                        <?php if (!empty($pendaftaran['tanggal_tes'])): ?>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Jadwal Tes:</div>
                            <div class="col-sm-8">
                                <i class="bi bi-calendar-event text-primary"></i> 
                                <?php echo formatTanggal($pendaftaran['tanggal_tes']); ?>
                                <br>
                                <i class="bi bi-clock text-primary"></i> 
                                <?php echo date('H:i', strtotime($pendaftaran['waktu_tes'])); ?> WIB
                                <?php if (!empty($pendaftaran['ruangan'])): ?>
                                    <br><i class="bi bi-door-open text-primary"></i> 
                                    Ruangan: <?php echo htmlspecialchars($pendaftaran['ruangan']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Biaya:</div>
                            <div class="col-sm-8">
                                <h4 class="text-success mb-0"><?php echo formatRupiah($pendaftaran['biaya']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembayaran Section - Hanya tampil jika status 'divalidasi' dan belum upload bukti -->
                <?php if ($pendaftaran['status'] == 'divalidasi' && empty($pendaftaran['bukti_pembayaran'])): ?>
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
                            <p class="mb-0"><strong>Jumlah Transfer:</strong> <span class="text-danger"><?php echo formatRupiah($pendaftaran['biaya']); ?></span></p>
                        </div>

                        <div class="alert alert-warning">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Petunjuk Pembayaran</h6>
                            <ol class="mb-0 ps-3">
                                <li>Transfer sesuai <strong>nominal yang tertera</strong></li>
                                <li>Simpan bukti transfer</li>
                                <li>Upload bukti transfer melalui form di bawah</li>
                                <li>Tunggu validasi dari admin (maksimal 1x24 jam)</li>
                            </ol>
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

                <!-- Bukti Pembayaran - Tampilan berbeda berdasarkan status -->
                <?php if (!empty($pendaftaran['bukti_pembayaran'])): ?>
                <div class="card <?php echo $pendaftaran['status'] == 'dibayar' || $pendaftaran['status'] == 'selesai' ? 'border-success' : 'border-primary'; ?> mb-4">
                    <div class="card-header <?php echo $pendaftaran['status'] == 'dibayar' || $pendaftaran['status'] == 'selesai' ? 'bg-success' : 'bg-primary'; ?> text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($pendaftaran['status'] == 'dibayar' || $pendaftaran['status'] == 'selesai'): ?>
                            <!-- Status: Pembayaran Dikonfirmasi -->
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Pembayaran telah dikonfirmasi oleh admin!</strong>
                                <?php if (!empty($pendaftaran['tanggal_pembayaran'])): ?>
                                    <br><small>Dikonfirmasi pada: <?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_pembayaran'])); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Status: Menunggu Validasi -->
                            <div class="alert alert-info">
                                <i class="bi bi-clock-history me-2"></i>
                                <strong>Bukti pembayaran telah diupload. Menunggu validasi admin.</strong>
                                <br><small>Proses validasi maksimal 1x24 jam</small>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Preview Bukti Pembayaran -->
                        <h6 class="fw-bold mb-3">Preview Bukti Transfer:</h6>
                        <?php 
                        $file_ext = strtolower(pathinfo($pendaftaran['bukti_pembayaran'], PATHINFO_EXTENSION));
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): 
                        ?>
                            <img src="uploads/bukti_pembayaran/<?php echo htmlspecialchars($pendaftaran['bukti_pembayaran']); ?>" 
                                 class="img-fluid rounded border" alt="Bukti Pembayaran">
                        <?php elseif ($file_ext == 'pdf'): ?>
                            <a href="uploads/bukti_pembayaran/<?php echo htmlspecialchars($pendaftaran['bukti_pembayaran']); ?>" 
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
                <!-- Status Saat Ini -->
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status Saat Ini</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $status_badge = getStatusBadge($pendaftaran['status']);
                        ?>
                        <div class="alert alert-<?php 
                            echo match($pendaftaran['status']) {
                                'menunggu_validasi' => 'warning',
                                'divalidasi' => 'info',
                                'menunggu_pembayaran' => 'primary',
                                'dibayar' => 'success',
                                'selesai' => 'secondary',
                                default => 'secondary'
                            };
                        ?> mb-0">
                            <strong><?php echo $status_badge['text']; ?></strong>
                        </div>
                        
                        <?php if ($pendaftaran['status'] == 'menunggu_validasi'): ?>
                            <p class="text-muted mt-3 mb-0">
                                <small><i class="bi bi-info-circle me-1"></i>Admin sedang memvalidasi pendaftaran Anda.</small>
                            </p>
                        <?php elseif ($pendaftaran['status'] == 'divalidasi' && empty($pendaftaran['bukti_pembayaran'])): ?>
                            <p class="text-muted mt-3 mb-0">
                                <small><i class="bi bi-exclamation-circle me-1"></i>Silakan lakukan pembayaran dan upload bukti transfer.</small>
                            </p>
                        <?php elseif ($pendaftaran['status'] == 'menunggu_pembayaran'): ?>
                            <p class="text-muted mt-3 mb-0">
                                <small><i class="bi bi-clock-history me-1"></i>Bukti pembayaran Anda sedang divalidasi oleh admin.</small>
                            </p>
                        <?php elseif ($pendaftaran['status'] == 'dibayar'): ?>
                            <p class="text-muted mt-3 mb-0">
                                <small><i class="bi bi-check-circle me-1"></i>Pembayaran telah dikonfirmasi. Harap hadir sesuai jadwal tes.</small>
                            </p>
                        <?php elseif ($pendaftaran['status'] == 'selesai'): ?>
                            <p class="text-muted mt-3 mb-0">
                                <small><i class="bi bi-trophy me-1"></i>Tes telah selesai. Cek sertifikat Anda di menu Sertifikat.</small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bantuan -->
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