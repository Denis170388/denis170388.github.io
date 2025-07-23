<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if ($_SESSION['role'] != 'admin') {
    header('Location: ../logout.php');
    exit();
}

// Panggil header untuk konsistensi tampilan
require_once '../includes/header.php';

// Ambil semua dokumen yang belum diverifikasi
$sql = "SELECT dokumen.*, users.nama_desa FROM dokumen
        JOIN users ON dokumen.user_id = users.id
        WHERE dokumen.status = 'Menunggu Verifikasi'
        ORDER BY dokumen.tgl_upload ASC";
$result = $koneksi->query($sql);
?>

<div class="container-fluid py-4">
    <h1 class="mt-0 mb-3">Dokumen Menunggu Verifikasi</h1>

    <?php
    if (isset($_GET['status_update']) && $_GET['status_update'] == 'success') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Status dokumen berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Dokumen</h6>
            </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Desa</th>
                            <th>Judul Dokumen</th>
                            <th>Tanggal Upload</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_desa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['judul_dokumen'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d F Y H:i', strtotime($row['tgl_upload']))); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($row['status']) {
                                            case 'Menunggu Verifikasi':
                                                $status_class = 'bg-warning text-dark';
                                                break;
                                            case 'Diterima':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'Ditolak':
                                                $status_class = 'bg-danger';
                                                break;
                                            default:
                                                $status_class = 'bg-secondary';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="verifikasi_dokumen.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Verifikasi</a>
                                    </td>
                                </tr>
                            <?php }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Tidak ada dokumen untuk diverifikasi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Panggil footer
require_once '../includes/footer.php';
?>