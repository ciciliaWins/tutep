<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mahasiswa = getMahasiswaData($conn);
    $nim = $mahasiswa['nim']; // Menggunakan nim sebagai identifier
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $nama = $_POST['nama']; // Sesuai dengan form name="nama"
    $fakultas = $_POST['fakultas'];
    $no_identitas = $_POST['no_identitas'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $no_hp = $_POST['no_hp'];
    $biaya = 25000.00; // Biaya tetap untuk cetak sertifikat
    
    // Validasi input
    $errors = [];
    
    // 1. Validasi pendaftaran_id
    if (empty($pendaftaran_id)) {
        $errors[] = 'Silakan pilih hasil tes!';
    }
   
    // 2. Validasi NIK (16 digit)
    if (!preg_match('/^[0-9]{16}$/', $no_identitas)) {
        $errors[] = 'NIK harus 16 digit angka!';
    }
    
    // 3. Validasi No HP
    if (!preg_match('/^08[0-9]{8,11}$/', $no_hp)) {
        $errors[] = 'Format nomor HP tidak valid! Gunakan format: 08xxxxxxxxxx';
    }
    
    // 4. Validasi tanggal lahir
    if (empty($tanggal_lahir)) {
        $errors[] = 'Tanggal lahir harus diisi!';
    }
    
    // Jika ada error, redirect ke sertifikat.php
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: sertifikat.php');
        exit();
    }
    
    // Insert pemesanan - sesuaikan dengan struktur tabel pemesanan_sertifikat
    $stmt = $conn->prepare("
        INSERT INTO pemesanan_sertifikat 
        (nim, pendaftaran_id, nama, fakultas, no_identitas, tanggal_lahir, no_hp, biaya, status, tanggal_pesan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu_validasi', NOW())
    ");
    $stmt->bind_param("sisssssd", $nim, $pendaftaran_id, $nama, $fakultas, $no_identitas, $tanggal_lahir, $no_hp, $biaya);
    
    if ($stmt->execute()) {
        $pesanan_id = $stmt->insert_id;
        $_SESSION['success'] = 'Pesanan sertifikat berhasil dibuat! Silakan lakukan pembayaran.';
        header('Location: detail_pesan_sertifikat.php?id=' . $pesanan_id);
        exit();
    } else {
        $_SESSION['error'] = 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.';
        header('Location: sertifikat.php');
        exit();
    }
}

// Jika bukan POST request, redirect ke sertifikat.php
header('Location: sertifikat.php');
exit();
?>