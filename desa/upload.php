<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

// Ambil daftar jenis dokumen dari tabel syarat_dokumen
$syarat_dokumen = $koneksi->query("SELECT nama_syarat FROM syarat_dokumen ORDER BY id ASC");

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $tahun_anggaran = $_POST['tahun_anggaran'];
    $deskripsi = $_POST['deskripsi'];
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        $target_dir = "../uploads/";
        $nama_file_asli = basename($_FILES["file_dokumen"]["name"]);
        // Buat nama file unik untuk menghindari tumpang tindih
        $nama_file_unik = uniqid() . '-' . $nama_file_asli;
        $target_file = $target_dir . $nama_file_unik;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["file_dokumen"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO dokumen (user_id, jenis_dokumen, tahun_anggaran, deskripsi, nama_dokumen_asli, nama_file) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("isssss", $user_id, $jenis_dokumen, $tahun_anggaran, $deskripsi, $nama_file_asli, $nama_file_unik);

                if ($stmt->execute()) {
                    $pesan = ['tipe' => 'success', 'teks' => 'Dokumen berhasil diupload.'];
                } else {
                    $pesan = ['tipe' => 'danger', 'teks' => 'Gagal menyimpan ke database.'];
                }
            } else {
                $pesan = ['tipe' => 'danger', 'teks' => 'Gagal memindahkan file.'];
            }
        } else {
            $pesan = ['tipe' => 'danger', 'teks' => 'Format file tidak diizinkan. Hanya PDF, JPG, PNG.'];
        }
    } else {
        $pesan = ['tipe' => 'danger', 'teks' => 'Tidak ada file yang dipilih atau terjadi error.'];
    }
}
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Upload Dokumen Persyaratan</h1>

    <?php if (isset($pesan)): ?>
        <div class="alert alert-<?php echo $pesan['tipe']; ?>">
            <?php echo $pesan['teks']; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            Formulir Pengajuan Berkas
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="jenis_dokumen" class="form-label">Jenis yang diupload</label>
                    <select class="form-select" id="jenis_dokumen" name="jenis_dokumen" required>
                        <option value="" selected disabled>-- Pilih Jenis Dokumen --</option>
                        <?php while ($row = $syarat_dokumen->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['nama_syarat']); ?>"><?php echo htmlspecialchars($row['nama_syarat']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tahun_anggaran" class="form-label">Tahun Anggaran</label>
                    <input type="number" class="form-control" id="tahun_anggaran" name="tahun_anggaran" min="2020" max="2099" step="1" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="file_dokumen" class="form-label">Upload Dokumen (PDF/JPG/PNG)</label>
                    <input class="form-control" type="file" id="file_dokumen" name="file_dokumen" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload
                </button>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>