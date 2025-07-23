<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Direktori tempat gambar profil akan disimpan
// Pastikan direktori ini ada dan memiliki izin tulis yang benar (misalnya 755 atau 777)
$upload_dir = '../uploads/profile_images/';

// Buat direktori jika belum ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // 0755 adalah izin yang disarankan, true untuk rekursif
}

if ($_SESSION['role'] != 'desa') {
    // Sesuaikan pengalihan logout jika sivijanda_db/logout.php yang benar
    // Menggunakan jalur absolut dari root server web lebih aman di sini
    header('Location: /sivijanda_db/logout.php'); // Contoh perbaikan jalur logout
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan = [];

// Proses form jika ada data yang dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nama_desa = trim($_POST['nama_desa']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($nama_lengkap) || empty($nama_desa)) {
        $pesan = ['tipe' => 'danger', 'teks' => 'Nama Lengkap dan Nama Desa tidak boleh kosong.'];
    } else {
        $update_password = false;
        $update_image = false;
        $image_filename = null; // Untuk menyimpan nama file gambar baru

        // Logika untuk upload gambar
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $file_name = $_FILES['profile_image']['name'];
            $file_size = $_FILES['profile_image']['size'];
            $file_type = $_FILES['profile_image']['type'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $extensions = ['jpeg', 'jpg', 'png', 'gif'];
            $max_file_size = 2 * 1024 * 1024; // 2 MB

            if (in_array($file_ext, $extensions) === false) {
                $pesan = ['tipe' => 'danger', 'teks' => 'Ekstensi file tidak diizinkan, gunakan JPG, JPEG, PNG, atau GIF.'];
            } elseif ($file_size > $max_file_size) {
                $pesan = ['tipe' => 'danger', 'teks' => 'Ukuran file terlalu besar, maksimal 2MB.'];
            } else {
                // Hapus gambar lama jika ada
                $stmt_get_old_image = $koneksi->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt_get_old_image->bind_param("i", $user_id);
                $stmt_get_old_image->execute();
                $old_image_data = $stmt_get_old_image->get_result()->fetch_assoc();
                if ($old_image_data && !empty($old_image_data['profile_image'])) {
                    $old_image_path = $upload_dir . $old_image_data['profile_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path); // Hapus file lama
                    }
                }

                // Buat nama file unik
                $image_filename = uniqid('profile_') . '.' . $file_ext;
                $target_path = $upload_dir . $image_filename;

                if (move_uploaded_file($file_tmp, $target_path)) {
                    $update_image = true;
                } else {
                    $pesan = ['tipe' => 'danger', 'teks' => 'Gagal mengunggah gambar.'];
                }
            }
        }

        // Logika untuk update password (jika diisi)
        if (!empty($password_baru)) {
            if ($password_baru !== $konfirmasi_password) {
                $pesan = ['tipe' => 'danger', 'teks' => 'Konfirmasi password tidak cocok.'];
            } else {
                $update_password = true;
                $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            }
        }

        // Bangun query UPDATE berdasarkan kondisi
        if (empty($pesan)) {
            $sql_parts = [];
            $bind_params = '';
            $bind_values = [];

            $sql_parts[] = "nama_lengkap = ?";
            $bind_params .= 's';
            $bind_values[] = $nama_lengkap;

            $sql_parts[] = "nama_desa = ?";
            $bind_params .= 's';
            $bind_values[] = $nama_desa;

            if ($update_password) {
                $sql_parts[] = "password = ?";
                $bind_params .= 's';
                $bind_values[] = $hash_password_baru;
            }
            if ($update_image) {
                $sql_parts[] = "profile_image = ?";
                $bind_params .= 's';
                $bind_values[] = $image_filename;
            }

            $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?";
            $bind_params .= 'i';
            $bind_values[] = $user_id;

            $stmt = $koneksi->prepare($sql);
            if ($stmt === false) {
                $pesan = ['tipe' => 'danger', 'teks' => 'Gagal menyiapkan statement SQL: ' . $koneksi->error];
            } else {
                $stmt->bind_param($bind_params, ...$bind_values);

                if ($stmt->execute()) {
                    $_SESSION['nama_lengkap'] = $nama_lengkap; // Update nama di sesi juga
                    if ($update_image) {
                        $_SESSION['profile_image'] = $image_filename; // Update gambar di sesi
                    }
                    $pesan = ['tipe' => 'success', 'teks' => 'Profil berhasil diperbarui.'];
                } else {
                    $pesan = ['tipe' => 'danger', 'teks' => 'Gagal memperbarui profil: ' . $stmt->error];
                }
                $stmt->close();
            }
        }
    }
}

// Ambil data terbaru dari database untuk ditampilkan di form
$stmt_get = $koneksi->prepare("SELECT username, nama_lengkap, nama_desa, profile_image FROM users WHERE id = ?");
$stmt_get->bind_param("i", $user_id);
$stmt_get->execute();
$user_data = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

require_once '../includes/header.php'; // Panggil header setelah logika
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Profil Desa</h1>

    <?php if (!empty($pesan)): ?>
        <div class="alert alert-<?php echo $pesan['tipe']; ?>">
            <?php echo $pesan['teks']; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3 text-center">
                    <?php
                    $profile_image_src = '';
                    if (!empty($user_data['profile_image'])) {
                        // Pastikan ini adalah jalur relatif yang benar dari file yang diakses browser
                        // Contoh: dari http://localhost/sivijanda_db/pages/desa/profil.php ke http://localhost/sivijanda_db/uploads/profile_images/
                        // Jika akses dari root sivijanda_db, maka 'uploads/profile_images/'
                        // Jika akses dari pages/desa, maka '../uploads/profile_images/'
                        // Disarankan menggunakan jalur absolut dari root server web:
                        $profile_image_src = '/sivijanda_db/uploads/profile_images/' . htmlspecialchars($user_data['profile_image']);
                    } else {
                        // Gambar placeholder jika tidak ada gambar profil
                        // Jalur ini juga harus dipertimbangkan dari root server web:
                        $profile_image_src = '/sivijanda_db/assets/img/default-profile.png'; // Pastikan Anda memiliki gambar placeholder ini
                    }
                    ?>
                    <img src="<?php echo $profile_image_src; ?>" class="img-fluid rounded-circle mb-3" alt="Gambar Profil" style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;" id="profileImageDisplay">
                </div>

                <div class="mb-3" style="display: none;">
                    <label for="profile_image" class="form-label">Unggah Gambar Profil</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    <div class="form-text">Maksimal 2MB (JPG, JPEG, PNG, atau GIF).</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                    <div class="form-text">Username tidak dapat diubah.</div>
                </div>
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap Kontak</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nama_desa" class="form-label">Nama Desa</label>
                    <input type="text" class="form-control" id="nama_desa" name="nama_desa" value="<?php echo htmlspecialchars($user_data['nama_desa']); ?>" required>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileImageDisplay = document.getElementById('profileImageDisplay');
        const profileImageInput = document.getElementById('profile_image');

        // Ketika gambar profil diklik, aktifkan input file
        profileImageDisplay.addEventListener('click', function() {
            profileImageInput.click();
        });

        // Opsional: Pratinjau gambar yang dipilih sebelum diunggah
        profileImageInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    profileImageDisplay.src = e.target.result;
                };

                reader.readAsDataURL(event.target.files[0]);
            }
        });
    });
</script>