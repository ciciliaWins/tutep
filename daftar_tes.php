<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);

// Get all jenis tes
$jenis_tes = $conn->query("SELECT * FROM jenis_tes ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Daftar Tes - TUTEP UPT Bahasa UNTAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        .navbar-gradient {background: linear-gradient(135deg, #2C3E50 0%,  #8BC34A 100%)}
        .bg-grey {background-color: #2C3E50 !important;}
        .text-grey {color: #2C3E50 !important;}
        .border-green { border-color: #8BC34A !important; }
        .btn-outline-blue {color:  #0D47A1 !important; border-color:  #0D47A1 !important;}
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
                <h2><i class="bi bi-file-earmark-text me-2"></i>Pendaftaran Tes</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Daftar Tes</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Test Cards -->
        <div class="row">
            <?php if ($jenis_tes && $jenis_tes->num_rows > 0): ?>
                <?php while ($tes = $jenis_tes->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-green h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($tes['nama_tes']); ?></h5>
                                    <span class="badge bg-grey text-light"><?php echo htmlspecialchars($tes['kode_tes']); ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Biaya Pendaftaran</small>
                                    <h4 class="text-success"><?php echo formatRupiah($tes['biaya']); ?></h4>
                                </div>
                                
                                <p class="card-text text-muted">
                                    <?php 
                                    if (!empty($tes['deskripsi'])) {
                                        echo htmlspecialchars(substr($tes['deskripsi'], 0, 150));
                                        if (strlen($tes['deskripsi']) > 150) echo '...';
                                    } else {
                                        echo 'Tidak ada deskripsi tersedia.';
                                    }
                                    ?>
                                </p>
                                
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="button" class="btn btn-outline-blue flex-fill" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $tes['id']; ?>">
                                        <i class="bi bi-info-circle me-1"></i>Detail
                                    </button>
                                    <?php if ($tes['kode_tes'] != 'TUTEP_PRIVAT'): ?>
                                        <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#jadwalModal<?php echo $tes['id']; ?>">
                                            <i class="bi bi-calendar-check me-1"></i>Daftar
                                        </button>
                                    <?php else: ?>
                                        <a href="form_pendaftaran.php?tes=<?php echo $tes['id']; ?>" class="btn btn-primary flex-fill">
                                            <i class="bi bi-calendar-check me-1"></i>Daftar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Modal -->
                    <div class="modal fade" id="detailModal<?php echo $tes['id']; ?>" tabindex="-1" aria-labelledby="detailModalLabel<?php echo $tes['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="detailModalLabel<?php echo $tes['id']; ?>">
                                        <?php echo htmlspecialchars($tes['nama_tes']); ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Kode Tes:</h6>
                                        <p><span class="badge bg-secondary text-emphasis"><?php echo htmlspecialchars($tes['kode_tes']); ?></span></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Biaya Pendaftaran:</h6>
                                        <h4 class="text-primary"><?php echo formatRupiah($tes['biaya']); ?></h4>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Deskripsi:</h6>
                                        <p><?php echo !empty($tes['deskripsi']) ? nl2br(htmlspecialchars($tes['deskripsi'])) : 'Tidak ada deskripsi tersedia.'; ?></p>
                                    </div>
                                    
                                    <?php if ($tes['kode_tes'] == 'TUTEP_REG' || $tes['kode_tes'] == 'TUTEP_REG_SERT' || $tes['kode_tes'] == 'TUTEP_WND' || $tes['kode_tes'] == 'TUTEP_WND_SERT'): ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Catatan:</strong> Jadwal yang tidak tersedia di tabel ini sudah TUTUP/FULL SLOT dan/atau terpotong HARI LIBUR.
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Penting:</strong> Untuk peserta mahasiswa S1 Untan, jika skor yang diperoleh dibawah 425 maka tidak bisa mendapatkan sertifikatnya.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($tes['kode_tes'] == 'TUTEP_PRIVAT'): ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Penting:</strong> Untuk peserta mahasiswa S1 Untan, jika skor yang diperoleh dibawah 425 maka tidak bisa mendapatkan sertifikatnya.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jadwal Modal -->
                    <?php if ($tes['kode_tes'] != 'TUTEP_PRIVAT'): ?>
                    <div class="modal fade" id="jadwalModal<?php echo $tes['id']; ?>" tabindex="-1" aria-labelledby="jadwalModalLabel<?php echo $tes['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="jadwalModalLabel<?php echo $tes['id']; ?>">
                                        Jadwal <?php echo htmlspecialchars($tes['nama_tes']); ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM jadwal_tes WHERE jenis_tes_id = ? AND status = 'aktif' AND tanggal_tes >= CURDATE() ORDER BY tanggal_tes, waktu_tes");
                                    $stmt->bind_param("i", $tes['id']);
                                    $stmt->execute();
                                    $jadwal_result = $stmt->get_result();
                                    ?>
                                    
                                    <?php if ($jadwal_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tanggal & Waktu</th>
                                                        <th>Kuota</th>
                                                        <th>Terisi</th>
                                                        <th>Tersedia</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($jadwal = $jadwal_result->fetch_assoc()): 
                                                        $slot_tersedia = $jadwal['kuota'] - $jadwal['slot_terisi'];
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo formatTanggal($jadwal['tanggal_tes']); ?></strong><br>
                                                                <small class="text-muted">
                                                                    <i class="bi bi-clock"></i> 
                                                                    <?php echo date('H:i', strtotime($jadwal['waktu_tes'])); ?> WIB
                                                                </small>
                                                                <?php if (!empty($jadwal['ruangan'])): ?>
                                                                    <br><small class="text-muted">
                                                                        <i class="bi bi-door-open"></i> 
                                                                        <?php echo htmlspecialchars($jadwal['ruangan']); ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo $jadwal['kuota']; ?></td>
                                                            <td><?php echo $jadwal['slot_terisi']; ?></td>
                                                            <td>
                                                                <span class="badge <?php echo $slot_tersedia > 10 ? 'bg-success' : ($slot_tersedia > 0 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                                                    <?php echo $slot_tersedia; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($slot_tersedia > 0): ?>
                                                                    <a href="form_pendaftaran.php?tes=<?php echo $tes['id']; ?>&jadwal=<?php echo $jadwal['id']; ?>" class="btn btn-sm btn-primary">
                                                                        <i class="bi bi-check-circle me-1"></i>Pilih
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button class="btn btn-sm btn-secondary" disabled>Penuh</button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Belum ada jadwal tersedia untuk tes ini.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                            <h4 class="mt-3">Belum Ada Tes Tersedia</h4>
                            <p class="text-muted">Saat ini belum ada jenis tes yang dapat didaftarkan. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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