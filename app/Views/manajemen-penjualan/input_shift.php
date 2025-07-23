<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-5">
    <h3 class="mb-4 font-weight-bold text-dark">Absensi Shift Kerja</h3>

    <form action="<?= base_url('manajemen-penjualan/simpan-shift') ?>" method="post" id="formShift">
        <!-- Outlet -->
        <div class="form-group">
            <label for="outlet_id">Outlet</label>
            <select name="outlet_id" class="form-control" <?= isset($readonly_outlet) && $readonly_outlet ? 'disabled' : '' ?>>
                <?php foreach ($outlets as $outlet): ?>
                    <option value="<?= $outlet['id'] ?>" selected><?= esc($outlet['nama_outlet']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($readonly_outlet) && $readonly_outlet): ?>
                <input type="hidden" name="outlet_id" value="<?= $outlets[0]['id'] ?>">
            <?php endif; ?>
        </div>

        <!-- Tanggal -->
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <!-- Pegawai -->
        <div class="form-group">
            <label for="user_id">Pilih Pegawai</label>
            <select name="user_id" id="user_id" class="form-control" required onchange="document.getElementById('cameraSection').style.display = this.value ? 'block' : 'none';">
                <option value="">-- Pilih Pegawai --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= esc($user['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Shift -->
        <div class="form-group">
            <label for="shift_id">Shift</label>
            <select name="shift_id" class="form-control" required>
                <option value="">-- Pilih Shift --</option>
                <?php foreach ($shifts as $shift): ?>
                    <option value="<?= $shift['id'] ?>">
                        <?= esc($shift['nama_shift']) ?> (<?= $shift['jam_mulai'] ?> - <?= $shift['jam_selesai'] ?>)
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Ambil Foto Bukti Absensi -->
        <div id="cameraSection" style="display: none;">
            <div class="form-group">
                <label>Ambil Foto Absensi</label><br>
                <button type="button" id="startCameraBtn" class="btn btn-primary mb-2">Aktifkan Kamera</button>
                <video id="video" autoplay style="display: none; width: 100%; max-width: 400px;" class="border rounded mb-2"></video>
                <button type="button" id="capture" class="btn btn-success mb-3" style="display: none;">Ambil Foto</button>
                <canvas id="canvas" style="display: none;"></canvas>
                <div id="preview"></div>
                <input type="hidden" name="foto_absensi" id="foto_absensi">
            </div>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary mt-3">
            <i class="fas fa-save mr-1"></i> Simpan Jadwal
        </button>
    </form>
</div>


<!-- Script -->
<script>
    // ===== Fetch Pegawai berdasarkan Outlet =====
    function fetchPegawai(outletId, targetSelect) {
        targetSelect.innerHTML = '<option value="">-- Pilih Pegawai --</option>';
        if (!outletId) return;

        fetch("<?= base_url('manajemen-penjualan/get-users') ?>/" + outletId)
            .then(res => res.json())
            .then(data => {
                data.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = user.username;
                    targetSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error("Gagal mengambil data pegawai:", err);
            });
    }

    // Load pegawai saat outlet dipilih
    document.getElementById('outletSelect')?.addEventListener('change', function() {
        const outletId = this.value;
        document.querySelectorAll('.pegawai-select').forEach(select => {
            fetchPegawai(outletId, select);
        });
    });

    // Tambah Pegawai Dinamis
    document.querySelector('.add-pegawai')?.addEventListener('click', function(e) {
        e.preventDefault();
        const outletId = document.getElementById('outletSelect').value;
        if (!outletId) {
            alert('Pilih outlet terlebih dahulu.');
            return;
        }

        const container = document.getElementById('pegawai-container');
        const row = document.createElement('div');
        row.classList.add('form-row', 'mb-2');
        row.innerHTML = `
            <div class="col-md-10">
                <select name="user_id[]" class="form-control pegawai-select" required>
                    <option value="">-- Pilih Pegawai --</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-block remove-pegawai">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        container.appendChild(row);

        const newSelect = row.querySelector('.pegawai-select');
        fetchPegawai(outletId, newSelect);

        row.querySelector('.remove-pegawai').addEventListener('click', function() {
            row.remove();
        });
    });

    // ===== Kamera & Foto Absensi dengan Watermark =====
    let videoStream = null;
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const fotoInput = document.getElementById('foto_absensi');
    const preview = document.getElementById('preview');
    const startBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('capture');

    // Aktifkan Kamera saat tombol diklik
    startBtn?.addEventListener('click', () => {
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(stream => {
                videoStream = stream;
                video.srcObject = stream;
                video.style.display = 'block';
                captureBtn.style.display = 'inline-block';
            })
            .catch(err => {
                alert('Tidak bisa mengakses kamera: ' + err);
            });
    });

    // Ambil Foto dan simpan ke input hidden
    captureBtn?.addEventListener('click', () => {
        if (!video || !canvas) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataURL = canvas.toDataURL('image/jpeg');
        fotoInput.value = dataURL;

        // Preview
        preview.innerHTML = `<img src="${dataURL}" class="img-fluid border mt-2" style="max-width: 300px;">`;

        // Matikan kamera (opsional)
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
            video.style.display = 'none';
            captureBtn.style.display = 'none';
        }
    });
</script>


<?= $this->endSection() ?>