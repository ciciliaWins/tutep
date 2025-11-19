<?php
require_once 'config.php';
requireLogin();

$mahasiswa = getMahasiswaData($conn);
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $fakultas = trim($_POST['fakultas']);
        $prodi = trim($_POST['prodi']);
        
        $stmt = $conn->prepare("UPDATE mahasiswa SET nama = ?, email = ?, fakultas = ?, prodi = ? WHERE nim = ?");
        $stmt->bind_param("sssss", $nama, $email, $fakultas, $prodi, $mahasiswa['nim']);
        
        if ($stmt->execute()) {
            $success = 'Profil berhasil diperbarui!';
            $mahasiswa = getMahasiswaData($conn);
        } else {
            $error = 'Gagal memperbarui profil!';
        }
    }
    
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($old_password, $mahasiswa['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE mahasiswa SET password = ? WHERE nim = ?");
                    $stmt->bind_param("ss", $hashed_password, $mahasiswa['nim']);
                    
                    if ($stmt->execute()) {
                        $success = 'Password berhasil diubah!';
                    } else {
                        $error = 'Gagal mengubah password!';
                    }
                } else {
                    $error = 'Password baru minimal 6 karakter!';
                }
            } else {
                $error = 'Password baru dan konfirmasi tidak cocok!';
            }
        } else {
            $error = 'Password lama salah!';
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
    <title>Profil - TUTEP UPT Bahasa UNTAN</title>
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
                <h2><i class="bi bi-person-circle me-2"></i>Profil Saya</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Info Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-grey">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle text-grey" style="font-size: 5rem;"></i>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($mahasiswa['nama']); ?></h5>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($mahasiswa['nim']); ?></p>
                        <span class="badge bg-grey"><?php echo htmlspecialchars($mahasiswa['status']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Profile Forms -->
            <div class="col-lg-8">
                <!-- Edit Profile -->
                <div class="card border-grey mb-4">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-person-fill"></i> Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">NIM</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fakultas</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['fakultas']); ?>" readonly>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Program Studi</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['prodi']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['status']); ?>" readonly>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card border-grey">
                    <div class="card-header bg-grey text-white">
                        <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" class="form-control" name="old_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" id="new_password" minlength="6" required>
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-shield-lock me-2"></i>Ubah Password
                            </button>
                        </form>
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
    <script>
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>