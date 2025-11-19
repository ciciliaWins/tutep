<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);

// Get tes info
$tes_id = $_GET['tes'] ?? 0;
$jadwal_id = $_GET['jadwal'] ?? null;

$stmt = $conn->prepare("SELECT * FROM jenis_tes WHERE id = ?");
$stmt->bind_param("i", $tes_id);
$stmt->execute();
$tes = $stmt->get_result()->fetch_assoc();

if (!$tes) {
    header('Location: daftar_tes.php');
    exit();
}

// Get jadwal if specified
$jadwal = null;
if ($jadwal_id) {
    $stmt = $conn->prepare("SELECT * FROM jadwal_tes WHERE id = ? AND jenis_tes_id = ?");
    $stmt->bind_param("ii", $jadwal_id, $tes_id);
    $stmt->execute();
    $jadwal = $stmt->get_result()->fetch_assoc();
}

// VALIDASI 1: Cek apakah sudah pernah daftar di tes dan jadwal yang sama
if ($jadwal_id) {
    $stmt = $conn->prepare("SELECT id FROM pendaftaran WHERE nim = ? AND jenis_tes_id = ? AND jadwal_tes_id = ? AND status NOT IN ('ditolak')");
    $stmt->bind_param("sii", $mahasiswa['nim'], $tes_id, $jadwal_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Anda sudah pernah mendaftar di tes dan jadwal yang sama!';
        header('Location: daftar_tes.php');
        exit();
    }
}

// VALIDASI 2: Cek batas pendaftaran untuk tes dengan sertifikat
// Hitung jumlah pendaftaran aktif untuk tes bersertifikat (TUTEP_REG dan TUTEP_REG_SERT)
if (in_array($tes['kode_tes'], ['TUTEP_REG', 'TUTEP_REG_SERT'])) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM pendaftaran p
        JOIN jenis_tes jt ON p.jenis_tes_id = jt.id
        WHERE p.nim = ? 
        AND jt.kode_tes IN ('TUTEP_REG', 'TUTEP_REG_SERT')
        AND p.status NOT IN ('ditolak')
    ");
    $stmt->bind_param("s", $mahasiswa['nim']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_pendaftaran = $result['total'];
    
    // Cek batas berdasarkan jenjang
    $batas_daftar = ($mahasiswa['status'] == 'S1') ? 2 : 1;
    
    if ($total_pendaftaran >= $batas_daftar) {
        $jenjang_text = ($mahasiswa['status'] == 'S1') ? 'Mahasiswa S1 hanya dapat mendaftar maksimal 2 kali' : 'Mahasiswa S2/S3 hanya dapat mendaftar maksimal 1 kali';
        $_SESSION['error'] = $jenjang_text . ' untuk tes TUTEP Regular/Weekend (dengan sertifikat)!';
        header('Location: daftar_tes.php');
        exit();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_lengkap'];
    $nim = $_POST['nim'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $jadwal_selected = $_POST['jadwal_id'] ?? $jadwal_id;
    
    // VALIDASI lagi sebelum insert
    if ($jadwal_selected) {
        $stmt = $conn->prepare("SELECT id FROM pendaftaran WHERE nim = ? AND jenis_tes_id = ? AND jadwal_tes_id = ? AND status NOT IN ('ditolak')");
        $stmt->bind_param("sii", $nim, $tes_id, $jadwal_selected);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Anda sudah pernah mendaftar di tes dan jadwal yang sama!';
        }
    }
    
    // Validasi batas pendaftaran untuk tes bersertifikat
    if (!isset($error) && in_array($tes['kode_tes'], ['TUTEP_REG', 'TUTEP_REG_SERT'])) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM pendaftaran p
            JOIN jenis_tes jt ON p.jenis_tes_id = jt.id
            WHERE p.nim = ? 
            AND jt.kode_tes IN ('TUTEP_REG', 'TUTEP_REG_SERT')
            AND p.status NOT IN ('ditolak')
        ");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $total_pendaftaran = $result['total'];
        
        $batas_daftar = ($mahasiswa['status'] == 'S1') ? 2 : 1;
        
        if ($total_pendaftaran >= $batas_daftar) {
            $jenjang_text = ($mahasiswa['status'] == 'S1') ? 'Mahasiswa S1 hanya dapat mendaftar maksimal 2 kali' : 'Mahasiswa S2/S3 hanya dapat mendaftar maksimal 1 kali';
            $error = $jenjang_text . ' untuk tes TUTEP Regular/Weekend (dengan sertifikat)!';
        }
    }
    
    if (!isset($error)) {
        $biaya = $tes['biaya'];
        
        // Insert pendaftaran
        $stmt = $conn->prepare("INSERT INTO pendaftaran (nim, jenis_tes_id, jadwal_tes_id, nama, email, no_hp, biaya, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu_validasi')");
        $stmt->bind_param("siisssd", $nim, $tes_id, $jadwal_selected, $nama, $email, $no_hp, $biaya);
        
        if ($stmt->execute()) {
            // Update slot if jadwal exists
            if ($jadwal_selected) {
                $conn->query("UPDATE jadwal_tes SET slot_terisi = slot_terisi + 1 WHERE id = $jadwal_selected");
            }
            
            $_SESSION['success'] = 'Pendaftaran berhasil! Silakan tunggu validasi dari admin.';
            header('Location: detail_pendaftaran.php?id=' . $stmt->insert_id);
            exit();
        } else {
            $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
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
    <title>Daftar Tes - TUTEP UPT Bahasa UNTAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .navbar-gradient {background: linear-gradient(135deg, #2C3E50 0%,  #8BC34A 100%)}
        .bg-grey {background-color: #2C3E50 !important;}
        .text-grey {color: #2C3E50 !important;}
        .border-grey { border-color: #2C3E50 !important; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
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

    <div class="container mt-5 mb-5">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="bi bi-file-earmark-text me-2"></i>Form Pendaftaran Tes</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="daftar_tes.php">Daftar Tes</a></li>
                        <li class="breadcrumb-item active">Form Pendaftaran</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-grey mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Data Pendaftar</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>
                                <small class="form-text text-muted">Sesuai ijazah/transkrip</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">No. HP/WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_hp" placeholder="08xxxxxxxxxx" pattern="08[0-9]{8,11}" required>
                                <small class="form-text text-muted">Format: 08xxxxxxxxxx</small>
                            </div>

                            <?php if ($tes['kode_tes'] == 'TUTEP_PRIVAT'): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Tanggal & Waktu Tes <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="jadwal_privat" required>
                                    <small class="form-text text-muted">Pilih tanggal dan waktu sesuai jam kerja kantor UPT Bahasa Untan</small>
                                </div>
                            <?php elseif (!$jadwal_id): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Jadwal Tes <span class="text-danger">*</span></label>
                                    <select class="form-select" name="jadwal_id" required>
                                        <option value="">-- Pilih Jadwal --</option>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM jadwal_tes WHERE jenis_tes_id = ? AND status = 'aktif' AND tanggal_tes >= CURDATE() ORDER BY tanggal_tes, waktu_tes");
                                        $stmt->bind_param("i", $tes_id);
                                        $stmt->execute();
                                        $jadwal_list = $stmt->get_result();
                                        while ($j = $jadwal_list->fetch_assoc()):
                                            $slot_tersedia = $j['kuota'] - $j['slot_terisi'];
                                            if ($slot_tersedia > 0):
                                        ?>
                                            <option value="<?php echo $j['id']; ?>">
                                                <?php echo formatTanggal($j['tanggal_tes']) . ' - ' . date('H:i', strtotime($j['waktu_tes'])) . ' WIB (Tersedia: ' . $slot_tersedia . ')'; ?>
                                            </option>
                                        <?php 
                                            endif;
                                        endwhile; 
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Daftar Sekarang
                                </button>
                                <a href="daftar_tes.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-grey mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Ringkasan Pendaftaran</h5>
                        
                        <div class="mb-3">
                            <small class="text-muted">Jenis Tes</small>
                            <p class="mb-0 fw-bold"><?php echo htmlspecialchars($tes['nama_tes']); ?></p>
                        </div>

                        <?php if ($jadwal): ?>
                        <div class="mb-3">
                            <small class="text-muted">Jadwal Tes</small>
                            <p class="mb-0 fw-bold"><?php echo formatTanggal($jadwal['tanggal_tes']); ?></p>
                            <p class="mb-0"><?php echo date('H:i', strtotime($jadwal['waktu_tes'])); ?> WIB</p>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <small class="text-muted">Biaya</small>
                            <h4 class="text-success mb-0"><?php echo formatRupiah($tes['biaya']); ?></h4>
                        </div>

                        <?php if ($tes['kode_tes'] == 'TUTEP_PRIVAT' && $mahasiswa['status'] == 'S1'): ?>
                        <div class="alert alert-warning mb-0">
                            <small><i class="bi bi-exclamation-triangle me-2"></i>Skor minimal 425 untuk mendapatkan sertifikat</small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (in_array($tes['kode_tes'], ['TUTEP_REG', 'TUTEP_REG_SERT'])): ?>
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Batas Pendaftaran:</strong><br>
                                <?php if ($mahasiswa['status'] == 'S1'): ?>
                                    Mahasiswa S1 maksimal 2x pendaftaran
                                <?php else: ?>
                                    Mahasiswa S2/S3 maksimal 1x pendaftaran
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-grey">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                        <small class="text-muted">
                            <ul class="ps-3 mb-0">
                                <li>Pastikan data yang Anda masukkan benar</li>
                                <li>Pendaftaran akan divalidasi oleh admin</li>
                                <li>Anda akan menerima notifikasi setelah validasi</li>
                                <li>Pembayaran dilakukan setelah pendaftaran divalidasi</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-light">
        <div class="container text-center text-muted">
            <small>&copy; <?php echo date('Y'); ?> UPT Bahasa UNTAN. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>