<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tutep');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Base URL
define('BASE_URL', 'http://localhost/tutep/');

// Upload directory
define('UPLOAD_DIR', 'uploads/');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['nim']); // Menggunakan nim sebagai session key
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Function to get logged in mahasiswa data
function getMahasiswaData($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $nim = $_SESSION['nim'];
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to format currency
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to format date
function formatTanggal($date) {
    if (empty($date)) return '-';
    
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $date);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Function to get status badge info
function getStatusBadge($status) {
    $badges = [
        'menunggu_validasi' => ['class' => 'bg-warning text-dark', 'text' => 'Menunggu Validasi'],
        'divalidasi' => ['class' => 'bg-info text-white', 'text' => 'Divalidasi'],
        'menunggu_pembayaran' => ['class' => 'bg-primary', 'text' => 'Menunggu Pembayaran'],
        'dibayar' => ['class' => 'bg-success', 'text' => 'Dibayar'],
        'selesai' => ['class' => 'bg-secondary', 'text' => 'Selesai'],
        'ditolak' => ['class' => 'bg-danger', 'text' => 'Ditolak'],
    ];
    return $badges[$status] ?? ['class' => 'bg-secondary', 'text' => ucfirst(str_replace('_', ' ', $status))];
}
?>