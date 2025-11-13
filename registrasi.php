<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $fakultas = trim($_POST['fakultas']);
    $prodi = trim($_POST['prodi']); // Sesuai dengan nama kolom di database
    $status = $_POST['status']; // S1, S2, S3 sesuai enum di database
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    // 1. Validate NIM format (Huruf + 9-11 digit)
    if (!preg_match('/^[A-Z]\d{9,11}$/', $nim)) {
        $errors[] = 'Format NIM tidak valid! Format: [Huruf][9-11 digit angka]. Contoh: F1011201001';
    }
    
    // 2. Validate NIM status digit (digit ke-2)
    if (strlen($nim) >= 2) {
        $status_digit = $nim[1]; // Digit ke-2
        $valid_status = false;
        
        switch($status) {
            case 'S1':
                if ($status_digit == '1') $valid_status = true;
                break;
            case 'S2':
                if ($status_digit == '2') $valid_status = true;
                break;
            case 'S3':
                if ($status_digit == '3') $valid_status = true;
                break;
        }
        
        if (!$valid_status) {
            $errors[] = 'Status tidak sesuai dengan NIM! Digit ke-2 harus: 1=S1, 2=S2, 3=S3';
        }
    }
    
    // 3. Check duplicate NIM
    $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'NIM sudah terdaftar! Silakan login atau gunakan NIM lain.';
    }
    
    // 4. Check duplicate Email
    $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Email sudah terdaftar! Gunakan email lain.';
    }
    
    // 5. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    }
    
    // 6. Validate password
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter!';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Password dan konfirmasi password tidak sama!';
    }
    
    // 7. Validate required fields
    if (empty($nama)) $errors[] = 'Nama lengkap harus diisi!';
    if (empty($fakultas)) $errors[] = 'Fakultas harus diisi!';
    if (empty($prodi)) $errors[] = 'Program studi harus diisi!';
    
    // If no errors, register
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Sesuai dengan struktur tabel mahasiswa di database
        $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, email, prodi, fakultas, status, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nim, $nama, $email, $prodi, $fakultas, $status, $hashed_password);
        
        if ($stmt->execute()) {
            $success = 'Registrasi berhasil! Silakan login dengan NIM dan password Anda.';
            // Clear form
            $nim = $nama = $email = $fakultas = $prodi = '';
        } else {
            $errors[] = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Registrasi - TUTEP UPT Bahasa UNTAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container-md">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6">
                <div class="card register-card justify-content-center border-2 border-primary-subtle rounded-3">
                    <div class="text-center pt-4 pb-2">
                        <img src="gambar/logouptdatar.png" alt="Logo UPT Bahasa UNTAN" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <div class="login-header text-center">
                        <h3>TUTEP UPT BAHASA UNTAN</h3>
                        <p class="mb-0">BUAT AKUN MAHASISWA</p>
                    </div>
                    <div class="card-body p-5 pt-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="registerForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">NIM <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" class="form-control" name="nim" id="nim" 
                                                   value="<?php echo $_POST['nim'] ?? ''; ?>" 
                                                   required placeholder="Masukkan NIM"
                                                   pattern="[A-Z]\d{9,11}"
                                                   title="Format: Huruf diikuti 9-11 digit angka">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" id="status" required>
                                            <option value="">-- Pilih Jenjang --</option>
                                            <option value="S1" <?php echo (($_POST['status'] ?? '') == 'S1') ? 'selected' : ''; ?>>S1 (Sarjana)</option>
                                            <option value="S2" <?php echo (($_POST['status'] ?? '') == 'S2') ? 'selected' : ''; ?>>S2 (Magister)</option>
                                            <option value="S3" <?php echo (($_POST['status'] ?? '') == 'S3') ? 'selected' : ''; ?>>S3 (Doktor)</option>
                                        </select>
                                        <small id="statusHint" class="form-text"></small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" name="nama" 
                                               value="<?php echo $_POST['nama'] ?? ''; ?>" 
                                               required placeholder="Masukkan nama lengkap">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo $_POST['email'] ?? ''; ?>" 
                                               required placeholder="email@student.untan.ac.id">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Fakultas <span class="text-danger">*</span></label>
                                        <select class="form-select" name="fakultas" required>
                                            <option value="">-- Pilih Fakultas --</option>
                                            <option value="Fakultas Teknik" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Teknik') ? 'selected' : ''; ?>>Fakultas Teknik</option>
                                            <option value="Fakultas Ekonomi dan Bisnis" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Ekonomi dan Bisnis') ? 'selected' : ''; ?>>Fakultas Ekonomi dan Bisnis</option>
                                            <option value="Fakultas Hukum" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Hukum') ? 'selected' : ''; ?>>Fakultas Hukum</option>
                                            <option value="Fakultas Pertanian" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Pertanian') ? 'selected' : ''; ?>>Fakultas Pertanian</option>
                                            <option value="Fakultas MIPA" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas MIPA') ? 'selected' : ''; ?>>Fakultas MIPA</option>
                                            <option value="Fakultas Ilmu Sosial dan Ilmu Politik" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Ilmu Sosial dan Ilmu Politik') ? 'selected' : ''; ?>>Fakultas Ilmu Sosial dan Ilmu Politik</option>
                                            <option value="Fakultas Keguruan dan Ilmu Pendidikan" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Keguruan dan Ilmu Pendidikan') ? 'selected' : ''; ?>>Fakultas Keguruan dan Ilmu Pendidikan</option>
                                            <option value="Fakultas Kehutanan" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Kehutanan') ? 'selected' : ''; ?>>Fakultas Kehutanan</option>
                                            <option value="Fakultas Kedokteran" <?php echo (($_POST['fakultas'] ?? '') == 'Fakultas Kedokteran') ? 'selected' : ''; ?>>Fakultas Kedokteran</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="prodi" 
                                               value="<?php echo $_POST['prodi'] ?? ''; ?>" 
                                               required placeholder="Masukkan program studi">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" name="password" id="password" 
                                                   required placeholder="Buat password" minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                            <input type="password" class="form-control" name="confirm_password" 
                                                   id="confirm_password" required placeholder="Ulangi password">
                                        </div>
                                        <small class="text-danger" id="passwordError" style="display: none;">Password tidak sama!</small>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="agree" required>
                                    <label class="form-check-label" for="agree">
                                        Saya menyatakan bahwa data yang saya masukkan adalah benar dan saya adalah mahasiswa aktif UNTAN
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-2 mb-2">
                                    <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                                </button>
                                
                                <div class="text-center">
                                    <span class="text-muted">Sudah punya akun?</span>
                                    <a href="login.php" class="text-decoration-none">Login di sini</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                password.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });
        
        // Validate NIM and Status match
        const nimInput = document.getElementById('nim');
        const statusSelect = document.getElementById('status');
        const statusHint = document.getElementById('statusHint');
        
        function validateNIMStatus() {
            const nim = nimInput.value.toUpperCase();
            const status = statusSelect.value;
            
            if (nim.length >= 2 && status) {
                const statusDigit = nim.charAt(1);
                let expectedDigit = '';
                
                switch(status) {
                    case 'S1': expectedDigit = '1'; break;
                    case 'S2': expectedDigit = '2'; break;
                    case 'S3': expectedDigit = '3'; break;
                }
                
                if (statusDigit === expectedDigit) {
                    statusHint.className = 'text-success';
                    statusHint.innerHTML = '<i class="bi bi-check-circle-fill"></i> Sesuai!';
                } else {
                    statusHint.className = 'text-danger';
                    statusHint.innerHTML = '<i class="bi bi-x-circle-fill"></i> Digit ke-2 NIM harus ' + expectedDigit + ' untuk ' + status;
                }
            } else {
                statusHint.innerHTML = '';
            }
        }
        
        nimInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validateNIMStatus();
        });
        
        statusSelect.addEventListener('change', validateNIMStatus);
        
        // Validate password match
        const confirmPassword = document.getElementById('confirm_password');
        const password = document.getElementById('password');
        const passwordError = document.getElementById('passwordError');
        
        function checkPasswordMatch() {
            if (confirmPassword.value && password.value !== confirmPassword.value) {
                passwordError.style.display = 'block';
                confirmPassword.setCustomValidity('Password tidak sama');
            } else {
                passwordError.style.display = 'none';
                confirmPassword.setCustomValidity('');
            }
        }
        
        confirmPassword.addEventListener('input', checkPasswordMatch);
        password.addEventListener('input', checkPasswordMatch);
        
        // Form validation before submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }
        });
    </script>
</body>
</html>