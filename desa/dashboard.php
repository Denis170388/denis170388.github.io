<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if ($_SESSION['role'] != 'desa') {
    header('Location: ' . BASE_URL . 'logout.php');
    exit();
}
require_once '../includes/header.php';

// --- Logika Statistik ---
$user_id = $_SESSION['user_id'];
$tahun_ini = date('Y');

// Total syarat dokumen
$total_syarat = $koneksi->query("SELECT COUNT(id) as total FROM syarat_dokumen")->fetch_assoc()['total'];

// Dokumen sudah diupload tahun ini
$stmt_uploaded = $koneksi->prepare("SELECT COUNT(id) as total FROM dokumen WHERE user_id = ? AND tahun_anggaran = ?");
$stmt_uploaded->bind_param('is', $user_id, $tahun_ini);
$stmt_uploaded->execute();
$total_uploaded = $stmt_uploaded->get_result()->fetch_assoc()['total'];

// Dokumen menunggu verifikasi tahun ini
$stmt_pending = $koneksi->prepare("SELECT COUNT(id) as total FROM dokumen WHERE user_id = ? AND status = 'Menunggu Verifikasi' AND tahun_anggaran = ?");
$stmt_pending->bind_param('is', $user_id, $tahun_ini);
$stmt_pending->execute();
$total_pending = $stmt_pending->get_result()->fetch_assoc()['total'];

// Dokumen sudah diverifikasi (Diterima) tahun ini
$stmt_verified = $koneksi->prepare("SELECT COUNT(id) as total FROM dokumen WHERE user_id = ? AND status = 'Diterima' AND tahun_anggaran = ?");
$stmt_verified->bind_param('is', $user_id, $tahun_ini);
$stmt_verified->execute();
$total_verified = $stmt_verified->get_result()->fetch_assoc()['total'];

// Dokumen belum diupload
$total_belum_upload = $total_syarat - $total_uploaded;
if ($total_belum_upload < 0) $total_belum_upload = 0; // Jaga-jaga jika ada upload ganda
?>

<div class="container-fluid">
    <h1 class="mt-4">Dashboard Desa</h1>
    <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>. Berikut adalah ringkasan status dokumen Anda untuk Tahun Anggaran <?php echo $tahun_ini; ?>.</p>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">Sudah Diupload</h5>
                    <p class="card-text fs-2 fw-bold"><?php echo $total_uploaded; ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-danger">Belum Diupload</h5>
                    <p class="card-text fs-2 fw-bold"><?php echo $total_belum_upload; ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">Sudah Diverifikasi</h5>
                    <p class="card-text fs-2 fw-bold"><?php echo $total_verified; ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-warning">Menunggu Verifikasi</h5>
                    <p class="card-text fs-2 fw-bold"><?php echo $total_pending; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>