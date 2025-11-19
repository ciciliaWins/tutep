<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);

// Get recent registrations
$stmt = $conn->prepare("
    SELECT p.*, jt.nama_tes, jt.kode_tes, jd.tanggal_tes, jd.waktu_tes, jd.ruangan 
    FROM pendaftaran p 
    JOIN jenis_tes jt ON p.jenis_tes_id = jt.id 
    LEFT JOIN jadwal_tes jd ON p.jadwal_tes_id = jd.id 
    WHERE p.nim = ? 
    ORDER BY p.tanggal_daftar DESC 
    LIMIT 5
");
$stmt->bind_param("s", $mahasiswa['nim']);
$stmt->execute();
$pendaftaran = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Dashboard - TUTEP UPT Bahasa UNTAN</title>
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
                <img src="gambar/logouptbahasa.png" alt="Logo UPT Bahasa" height="30" class="me-2">TUTEP UPT BAHASA UNTAN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-grey">
                    <div class="card-body">
                        <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($mahasiswa['nama']); ?>!</h4>
                        <p class="card-text text-muted mb-0">
                            <i class="bi bi-person-badge me-1"></i>NIM: <?php echo htmlspecialchars($mahasiswa['nim']); ?><br>
                            <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($mahasiswa['fakultas']); ?><br>
                            <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($mahasiswa['prodi']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <a href="daftar_tes.php" class="text-decoration-none">
                    <div class="card border-grey h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text text-grey" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Daftar Tes</h5>
                            <p class="card-text text-muted">Daftar tes TUTEP UNTAN</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="sertifikat.php" class="text-decoration-none">
                    <div class="card border-grey h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-award text-grey" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Sertifikat</h5>
                            <p class="card-text text-muted">Lihat dan cetak sertifikat Anda</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Registrations -->
        <div class="row">
            <div class="col-12">
                <div class="card border-green">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Pendaftaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($pendaftaran->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Jenis Tes</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Jadwal Tes</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $pendaftaran->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['nama_tes']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['kode_tes']); ?></small>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                                                <td>
                                                    <?php if ($row['tanggal_tes']): ?>
                                                        <i class="bi bi-calendar-event text-primary"></i> 
                                                        <?php echo date('d/m/Y', strtotime($row['tanggal_tes'])); ?><br>
                                                        <i class="bi bi-clock text-primary"></i> 
                                                        <?php echo date('H:i', strtotime($row['waktu_tes'])); ?> WIB
                                                        <?php if ($row['ruangan']): ?>
                                                            <br><i class="bi bi-door-open text-primary"></i> 
                                                            <?php echo htmlspecialchars($row['ruangan']); ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Belum dijadwalkan</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $badge = getStatusBadge($row['status']);
                                                    ?>
                                                    <span class="badge <?php echo $badge['class']; ?>">
                                                        <?php echo $badge['text']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="detail_pendaftaran.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-3">Belum ada riwayat pendaftaran</p>
                                <a href="daftar_tes.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Daftar Tes Sekarang
                                </a>
                            </div>
                        <?php endif; ?>
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