<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if ($_SESSION['role'] != 'admin') { header('Location: ../logout.php'); exit(); }

$user_id = $_SESSION['user_id'];
$pesan = [];

// Proses form jika ada data yang dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($nama_lengkap)) {
        $pesan = ['tipe' => 'danger', 'teks' => 'Nama Lengkap tidak boleh kosong.'];
    } else {
        // Logika untuk update password (jika diisi)
        if (!empty($password_baru)) {
            if ($password_baru !== $konfirmasi_password) {
                $pesan = ['tipe' => 'danger', 'teks' => 'Konfirmasi password tidak cocok.'];
            } else {
                // Update nama dan password
                $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama_lengkap = ?, password = ? WHERE id = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("ssi", $nama_lengkap, $hash_password_baru, $user_id);
            }
        } else {
            // Update nama saja
            $sql = "UPDATE users SET nama_lengkap = ? WHERE id = ?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("si", $nama_lengkap, $user_id);
        }

        // Eksekusi query jika tidak ada error password
        if (empty($pesan)) {
            if ($stmt->execute()) {
                $_SESSION['nama_lengkap'] = $nama_lengkap; // Update nama di sesi juga
                $pesan = ['tipe' => 'success', 'teks' => 'Profil berhasil diperbarui.'];
            } else {
                $pesan = ['tipe' => 'danger', 'teks' => 'Gagal memperbarui profil.'];
            }
        }
    }
}

// Ambil data terbaru dari database untuk ditampilkan di form
$stmt_get = $koneksi->prepare("SELECT username, nama_lengkap FROM users WHERE id = ?");
$stmt_get->bind_param("i", $user_id);
$stmt_get->execute();
$user_data = $stmt_get->get_result()->fetch_assoc();

require_once '../includes/header.php'; // Panggil header setelah logika
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Profil Admin</h1>

    <?php if(!empty($pesan)): ?>
        <div class="alert alert-<?php echo $pesan['tipe']; ?>">
            <?php echo $pesan['teks']; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                    <div class="form-text">Username tidak dapat diubah.</div>
                </div>
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                </div>
                <hr>
                <h5 class="mb-3">Ubah Password</h5>
                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" id="password_baru" name="password_baru">
                    <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                </div>
                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>