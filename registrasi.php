<?php
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['password2'];

    if ($password !== $confirm) {
        $error = "Password dan konfirmasi password tidak sama.";
    }
    else {
        $cekEmail = mysqli_query($conn, "SELECT * FROM user WHERE Email = '$email'");
        if (mysqli_num_rows($cekEmail) > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO user (Username, Email, Password) VALUES ('$username', '$email', '$hashed_password')";
            $result = mysqli_query($conn, $query);

            if ($result) {
                $userBaru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE Email = '$email'"));

                $_SESSION['user'] = $userBaru;

                header("Location: beranda.php");
                exit();
            } else {
                $error = "Gagal mendaftarkan akun. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="gambar/logouptbahasa.png">
    <title>Login - TUTEP UPT Bahasa UNTAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container-md">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card login-card justify-content-center border-2 border-primary-subtle rounded-3">
                    <div class="text-center pt-4 pb-2">
                        <img src="gambar/logouptdatar.png" alt="Logo UPT Bahasa UNTAN" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <div class="login-header text-center">
                        <h3>TUTEP UPT BAHASA UNTAN</h3>
                        <p class="mb-0">PENDAFTARAN AKUN MAHASISWA</p>
                    </div>
                    <div class="card-body p-5 pt-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label">NIM</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control" name="nim" required placeholder="Masukkan NIM Anda">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" name="password" required placeholder="Masukkan Password">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Belum punya akun? <a href="registrasi.php" class="text-primary fw-bold">Daftar di sini</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>