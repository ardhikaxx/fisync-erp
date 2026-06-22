<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Aktivitas Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0D7377; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #0D7377; }
        .header p { margin: 5px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #0D7377; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Aktivitas Transaksi Terakhir</h2>
        <p>Diekspor pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Referensi</th>
                <th>Keterangan</th>
                <th class="text-end">Total (Rp)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}</td>
                <td>{{ $trx->reference_number ?? $trx->transaction_number }}</td>
                <td>{{ $trx->description }}</td>
                <td class="text-end">{{ number_format($trx->journal_entries_sum_debit, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($trx->status == 'posted')
                        <span class="badge badge-success">POSTED</span>
                    @else
                        <span class="badge badge-warning">{{ strtoupper($trx->status) }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
