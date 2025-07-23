<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM dokumen WHERE user_id = ? ORDER BY tgl_upload DESC";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Daftar Dokumen Saya</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Jenis Dokumen</th>
                            <th>Tahun</th>
                            <th>Tgl Upload</th>
                            <th class="text-center">Status Verifikasi</th>
                            <th>Catatan Admin</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['jenis_dokumen']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tahun_anggaran']); ?></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($row['tgl_upload'])); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $status = $row['status'];
                                        if ($status == 'Diterima') {
                                            echo '<span class="badge fs-6 bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-check-circle-fill me-2"></i>Diterima</span>';
                                        } elseif ($status == 'Ditolak') {
                                            echo '<span class="badge fs-6 bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-x-circle-fill me-2"></i>Ditolak</span>';
                                        } else {
                                            echo '<span class="badge fs-6 bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-hourglass-split me-2"></i>Menunggu Verifikasi</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['catatan'] ?? '<em>Tidak ada catatan</em>'); ?></td>
                                    <td>
                                        <a href="../uploads/<?php echo $row['nama_file']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye-fill"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Anda belum pernah mengupload dokumen.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>