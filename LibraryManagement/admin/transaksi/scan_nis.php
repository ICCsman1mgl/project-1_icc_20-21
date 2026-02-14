<?php
// admin/transaksi/scan_nis.php
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan QR NIS</title>

    <style>
        .wrap {
            max-width: 900px;
            margin: 20px auto;
            padding: 16px;
        }

        #reader {
            width: 320px;
        }

        .row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 14px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-size: 14px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
        }

        .btn {
            padding: 10px 14px;
            cursor: pointer;
        }

        .ok {
            color: #0a7;
            margin-top: 10px;
        }

        .bad {
            color: #c00;
            margin-top: 10px;
        }
    </style>

    <script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body>
    <a class="dropdown-item" href="/LibraryManagement/admin/transaksi/scan_nis.php">
        Scan NIS (QR)
    </a>

    <div class="wrap">
        <h2>Scan QR (NIS)</h2>

        <div class="row">
            <div class="card">
                <div id="reader"></div>
                <div style="margin-top:10px;">
                    <button class="btn" id="btnStart">Start</button>
                    <button class="btn" id="btnStop" disabled>Stop</button>
                </div>
                <div id="msg"></div>
            </div>

            <div class="card" style="flex:1; min-width: 280px;">
                <h3>Data Peminjam</h3>

                <label>NIS</label>
                <input id="nis" type="text" placeholder="Scan atau ketik NIS">

                <label>Nama</label>
                <input id="nama" type="text" placeholder="Otomatis terisi" readonly>

                <label>Alamat</label>
                <textarea id="alamat" rows="4" placeholder="Otomatis terisi" readonly></textarea>

                <div style="margin-top:12px;">
                    <button class="btn" id="btnCari">Cari Manual</button>
                    <a class="btn" href="pinjam.php" style="text-decoration:none;">Lanjut ke Pinjam</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const msg = document.getElementById('msg');
        const nisEl = document.getElementById('nis');
        const namaEl = document.getElementById('nama');
        const alamatEl = document.getElementById('alamat');
        const btnStart = document.getElementById('btnStart');
        const btnStop = document.getElementById('btnStop');
        const btnCari = document.getElementById('btnCari');

        let lastNis = '';
        let qr;

        function setMsg(text, type = 'ok') {
            msg.className = type === 'ok' ? 'ok' : 'bad';
            msg.textContent = text;
        }

        async function lookupNis(nis) {
            nis = (nis || '').trim();
            if (!nis) return;

            nisEl.value = nis;
            setMsg('Mencari NIS...', 'ok');

            const url = `../../proses/get_anggota_by_nis.php?nis=${encodeURIComponent(nis)}`;
            const res = await fetch(url);
            const json = await res.json();

            if (json.status === 'found') {
                namaEl.value = json.data.nama || '';
                alamatEl.value = json.data.alamat || '';
                setMsg('Data ditemukan.', 'ok');

                // optional: simpan ke localStorage biar kebawa ke pinjam.php
                localStorage.setItem('nis', nisEl.value);
                localStorage.setItem('nama', namaEl.value);
                localStorage.setItem('alamat', alamatEl.value);
            } else if (json.status === 'not_found') {
                namaEl.value = '';
                alamatEl.value = '';
                setMsg('NIS belum terdaftar di anggota.', 'bad');
            } else {
                setMsg('Input NIS tidak valid.', 'bad');
            }
        }

        function onScanSuccess(decodedText) {
            const nis = decodedText.trim();
            if (!nis) return;
            if (nis === lastNis) return; // anti spam
            lastNis = nis;
            lookupNis(nis);
        }

        async function startScan() {
            try {
                qr = new Html5Qrcode("reader");
                await qr.start({
                        facingMode: "environment"
                    }, {
                        fps: 10,
                        qrbox: 250
                    },
                    onScanSuccess
                );
                btnStart.disabled = true;
                btnStop.disabled = false;
                setMsg('Scanner aktif. Arahkan kamera ke QR.', 'ok');
            } catch (e) {
                setMsg('Gagal akses kamera. Izinkan kamera di browser.', 'bad');
            }
        }

        async function stopScan() {
            if (!qr) return;
            await qr.stop();
            await qr.clear();
            btnStart.disabled = false;
            btnStop.disabled = true;
            setMsg('Scanner berhenti.', 'ok');
        }

        btnStart.addEventListener('click', startScan);
        btnStop.addEventListener('click', stopScan);
        btnCari.addEventListener('click', () => lookupNis(nisEl.value));
    </script>
</body>

</html>