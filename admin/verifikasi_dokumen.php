<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['role'] != 'admin') {
    header('Location: ' . BASE_URL . 'logout.php');
    exit();
}

// Panggil header aplikasi Anda (misalnya sidebar, navbar, dll.)
require_once '../includes/header.php';

$error = ''; // Variabel untuk menyimpan pesan error

// --- PROSES UPDATE STATUS DOKUMEN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dokumen_id = $_POST['dokumen_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $catatan = $_POST['catatan'] ?? '';

    if (empty($dokumen_id) || empty($status)) {
        $error = "ID dokumen atau status tidak boleh kosong.";
    } else {
        $sql_update = "UPDATE dokumen SET status = ?, catatan = ? WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("ssi", $status, $catatan, $dokumen_id);
            if ($stmt_update->execute()) {
                header('Location: dokumen_masuk.php?status_update=success');
                exit();
            } else {
                $error = "Gagal memperbarui status dokumen: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error = "Gagal menyiapkan statement update: " . $koneksi->error;
        }
    }
}

// --- AMBIL DATA DOKUMEN BERDASARKAN ID DARI URL ---
$dokumen_id = $_GET['id'] ?? 0; // Mengambil ID dari URL. Jika tidak ada, default ke 0.

// Pastikan ID dokumen valid sebelum melakukan query ke database
if (!is_numeric($dokumen_id) || $dokumen_id <= 0) {
?>
    <main class="main-content">
        <div class="container-fluid py-4">
            <div class='alert alert-danger'>Tidak ada dokumen yang divalidasi.</div>
        </div>
    </main>
    <?php
    require_once '../includes/footer.php';
    exit(); // Hentikan eksekusi script jika ID tidak valid
}

$sql_select = "SELECT dokumen.*, users.nama_lengkap, users.nama_desa
               FROM dokumen
               JOIN users ON dokumen.user_id = users.id
               WHERE dokumen.id = ?";
$stmt_select = $koneksi->prepare($sql_select);

if ($stmt_select) {
    $stmt_select->bind_param("i", $dokumen_id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $dokumen = $result_select->fetch_assoc();

    if (!$dokumen) {
    ?>
        <main class="main-content">
            <div class="container-fluid py-4">
                <div class='alert alert-danger'>Dokumen tidak ditemukan.</div>
            </div>
        </main>
    <?php
        require_once '../includes/footer.php';
        exit();
    }
    $stmt_select->close();
} else {
    ?>
    <main class="main-content">
        <div class="container-fluid py-4">
            <div class='alert alert-danger'>Gagal mengambil data dokumen: <?php echo $koneksi->error; ?></div>
        </div>
    </main>
<?php
    require_once '../includes/footer.php';
    exit();
}

// ... (Sisa kode HTML verifikasi_dokumen.php di bawahnya tetap sama) ...
?>

<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header">
            Detail Dokumen untuk Verifikasi
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <h4>Informasi Dokumen</h4>
                    <hr>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Judul Dokumen:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo htmlspecialchars($dokumen['nama_dokumen'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Jenis Dokumen:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo htmlspecialchars($dokumen['jenis_dokumen'] ?? 'Tidak Diketahui'); ?></p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Tanggal Upload:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo htmlspecialchars(date('d F Y H:i', strtotime($dokumen['tgl_upload']))); ?></p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Status Saat Ini:</label>
                        <div class="col-sm-8">
                            <?php
                            $current_status = $dokumen['status'];
                            $status_class = '';
                            switch ($current_status) {
                                case 'Menunggu Verifikasi':
                                    $status_class = 'status-pending';
                                    break;
                                case 'Diterima':
                                    $status_class = 'status-disetujui';
                                    break;
                                case 'Ditolak':
                                    $status_class = 'status-ditolak';
                                    break;
                                default:
                                    $status_class = 'badge-secondary';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($current_status); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($dokumen['catatan'])): ?>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Catatan Admin:</label>
                            <div class="col-sm-8">
                                <p class="form-control-static"><?php echo nl2br(htmlspecialchars($dokumen['catatan'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h4 class="mt-4">Informasi Pengunggah</h4>
                    <hr>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Pengunggah:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo htmlspecialchars($dokumen['nama_lengkap']); ?></p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Nama Desa:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo htmlspecialchars($dokumen['nama_desa']); ?></p>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <h4>Pratinjau Dokumen</h4>
                    <hr>
                    <div class="dokumen-preview">
                        <?php
                        $file_path = '../uploads/' . htmlspecialchars($dokumen['nama_file']);
                        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

                        if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo '<img src="' . $file_path . '" alt="Pratinjau Gambar Dokumen">';
                        } elseif (strtolower($file_extension) == 'pdf') {
                            echo '<iframe src="' . $file_path . '#toolbar=0" width="100%" height="400px" style="border:none;"></iframe>';
                        } else {
                            echo '<p>Tidak ada pratinjau visual tersedia untuk jenis file ini.</p>';
                            echo '<a href="' . $file_path . '" target="_blank" class="btn btn-info mt-2">Unduh Dokumen</a>';
                        }
                        ?>
                    </div>
                    <p class="mt-2 text-center">
                        <a href="<?php echo $file_path; ?>" target="_blank" class="btn btn-secondary btn-sm">Buka di Tab Baru</a>
                        <a href="<?php echo $file_path; ?>" download class="btn btn-info btn-sm">Unduh Dokumen</a>
                    </p>
                </div>
            </div>

            <hr class="my-4">

            <h4>Formulir Verifikasi</h4>
            <form action="verifikasi_dokumen.php" method="POST">
                <input type="hidden" name="dokumen_id" value="<?php echo htmlspecialchars($dokumen['id']); ?>">

                <div class="form-group">
                    <label for="status">Ubah Status:</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="Menunggu Verifikasi" <?php echo ($dokumen['status'] == 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="Diterima" <?php echo ($dokumen['status'] == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                        <option value="Ditolak" <?php echo ($dokumen['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan (Opsional):</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan verifikasi atau alasan penolakan..."><?php echo htmlspecialchars($dokumen['catatan']); ?></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
                    <a href="dokumen_masuk.php" class="btn btn-secondary">Kembali ke Daftar Dokumen</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Panggil footer
require_once '../includes/footer.php';
?>