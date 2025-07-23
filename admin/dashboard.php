<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if ($_SESSION['role'] != 'admin') {
    // Jika bukan admin, tendang
    header('Location: ' . BASE_URL . 'logout.php');
    exit();
}

// Panggil header (ini akan membuka <main class="main-content">)
require_once '../includes/header.php';

// Logika untuk mengambil data statistik per desa
$sql = "SELECT
            u.nama_desa,
            COUNT(d.id) as total_dokumen,
            SUM(CASE WHEN d.status = 'Menunggu Verifikasi' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN d.status = 'Diterima' THEN 1 ELSE 0 END) as diterima
        FROM users u
        LEFT JOIN dokumen d ON u.id = d.user_id
        WHERE u.role = 'desa'
        GROUP BY u.id
        ORDER BY u.nama_desa ASC";
$result = $koneksi->query($sql);
?>

<div class="container-fluid py-4"> <h1 class="mt-0 mb-3">Dashboard Admin</h1> <p>Ringkasan dokumen yang masuk dari setiap desa.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Status Dokumen per Desa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Desa</th>
                            <th>Total Dokumen Diupload</th>
                            <th>Menunggu Verifikasi</th>
                            <th>Dokumen Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_desa']); ?></td>
                                    <td><?php echo $row['total_dokumen']; ?></td>
                                    <td>
                                        <span class="badge bg-warning"><?php echo $row['pending']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $row['diterima']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data dari desa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Panggil footer (ini akan menutup </main> dan </body></html>)
require_once '../includes/footer.php';
?>