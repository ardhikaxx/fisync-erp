<?php

namespace App\Exports;

use App\Models\Accounting\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::with(['branch'])
            ->withSum('journalEntries', 'debit')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(50) // Or all transactions if preferred, for now export the recent 50
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Referensi',
            'Keterangan',
            'Kantor / Cabang',
            'Total (Rp)',
            'Status'
        ];
    }

    public function map($transaction): array
    {
        return [
            Carbon::parse($transaction->transaction_date)->format('d M Y'),
            $transaction->reference_number ?? $transaction->transaction_number,
            $transaction->description,
            $transaction->branch ? $transaction->branch->branch_name : 'Pusat',
            $transaction->journal_entries_sum_debit,
            ucfirst($transaction->status)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF0D7377']]],
        ];
    }
}
