
<?php
include 'koneksi.php';

$labels = [];
$data_total = [];
$rekap = [];

$total_pendapatan = 0;
$total_pelanggan = 0;

$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {

    $mulai = mysqli_real_escape_string($conn, $tanggal_mulai);
    $selesai = mysqli_real_escape_string($conn, $tanggal_selesai);

    // Query laporan penjualan harian
    $q = "SELECT DATE(waktu_transaksi) AS tanggal,
          SUM(total) AS total_harian
          FROM transaksi
          WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'
          GROUP BY DATE(waktu_transaksi)
          ORDER BY tanggal ASC";

    $res = mysqli_query($conn, $q);

    while ($r = mysqli_fetch_assoc($res)) {
        $labels[] = $r['tanggal'];
        $data_total[] = $r['total_harian'];
        $rekap[] = $r;

        $total_pendapatan += $r['total_harian'];
    }

    // Query jumlah pelanggan unik
    $qp = "SELECT COUNT(DISTINCT pelanggan_id) AS jumlah
           FROM transaksi
           WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'";

    $rp = mysqli_fetch_assoc(mysqli_query($conn, $qp));

    $total_pelanggan = $rp['jumlah'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f4f4f4;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        input, button {
            padding: 8px;
            margin: 5px;
        }

        button {
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #ddd;
        }

        .ringkasan {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: #e8f0fe;
            padding: 20px;
            flex: 1;
            border-radius: 8px;
        }

        .no-print {
            display: block;
        }

        @media print {
            body {
                background: white;
                margin: 10px;
            }

            .no-print {
                display: none !important;
            }

            .container {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="no-print">
        <h2>Filter Laporan Penjualan</h2>

        <form method="GET">

            Tanggal Mulai:
            <input type="date" name="tanggal_mulai"
                value="<?= htmlspecialchars($tanggal_mulai); ?>" required>

            Tanggal Selesai:
            <input type="date" name="tanggal_selesai"
                value="<?= htmlspecialchars($tanggal_selesai); ?>" required>

            <button type="submit">Tampilkan</button>

        </form>

        <button onclick="window.print()">Cetak</button>

        <a href="export_excel.php?tanggal_mulai=<?= urlencode($tanggal_mulai); ?>&tanggal_selesai=<?= urlencode($tanggal_selesai); ?>">
            <button type="button">Excel</button>
        </a>
    </div>

    <?php if (!empty($tanggal_mulai) && !empty($tanggal_selesai)): ?>

        <h2>Laporan Penjualan</h2>

        <p>
            Periode:
            <?= htmlspecialchars($tanggal_mulai); ?>
            sampai
            <?= htmlspecialchars($tanggal_selesai); ?>
        </p>

        <?php if (!empty($labels)): ?>

            <h3>Grafik Penjualan</h3>

            <div style="width: 100%; max-width: 800px; height: 350px;">
                <canvas id="myChart"></canvas>
            </div>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'bar',

                    data: {
                        labels: <?= json_encode($labels); ?>,

                        datasets: [{
                            label: 'Total Pendapatan',
                            data: <?= json_encode($data_total); ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>

            <h3>Rekapitulasi Penjualan</h3>

            <table>
                <tr>
                    <th>No</th>
                    <th>Total Pendapatan</th>
                    <th>Tanggal Transaksi</th>
                </tr>

                <?php
                $no = 1;

                foreach ($rekap as $row):
                ?>

                <tr>
                    <td><?= $no++; ?></td>

                    <td>
                        Rp <?= number_format($row['total_harian'], 0, ',', '.'); ?>
                    </td>

                    <td><?= $row['tanggal']; ?></td>
                </tr>

                <?php endforeach; ?>

            </table>

            <div class="ringkasan">

                <div class="card">
                    <h3>Jumlah Pelanggan Unik</h3>
                    <h2><?= $total_pelanggan; ?> Orang</h2>
                </div>

                <div class="card">
                    <h3>Total Pendapatan</h3>
                    <h2>
                        Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?>
                    </h2>
                </div>

            </div>

        <?php else: ?>

            <p>Tidak ada transaksi pada rentang tanggal tersebut.</p>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
