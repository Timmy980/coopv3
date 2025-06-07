<?php

namespace App\Exports;

use App\Models\MemberAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class SavingsTemplateExport extends StringValueBinder implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomValueBinder, WithMapping
{
    protected $accountTypeId;

    public function __construct($accountTypeId)
    {
        $this->accountTypeId = $accountTypeId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return MemberAccount::where('account_type_id', $this->accountTypeId)
            ->with('user')
            ->get()
            ->map(function ($account) {
                return [
                    'account_number' => $account->account_number,
                    'full_name' => $account->user->first_name . ' ' . $account->user->last_name,
                    'amount' => '',
                    'transaction_date' => now()->format('Y-m-d'),
                    'reference_number' => '',
                    'notes' => ''
                ];
            });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Account Number',
            'Full Name',
            'Amount',
            'Transaction Date',
            'Reference Number',
            'Notes'
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Make the header row bold
        $sheet->getStyle('1')->getFont()->setBold(true);
        
        // Lock the account number and full name columns
        $sheet->protectCells('A2:B' . ($sheet->getHighestRow()), 'password');
        
        // Add data validation for amount (must be numeric and greater than 0)
        $sheet->getStyle('C2:C' . $sheet->getHighestRow())
            ->getProtection()
            ->setLocked(false);
            
        // Add date validation
        $sheet->getStyle('D2:D' . $sheet->getHighestRow())
            ->getProtection()
            ->setLocked(false);
            
        // Allow editing of reference and notes
        $sheet->getStyle('E2:F' . $sheet->getHighestRow())
            ->getProtection()
            ->setLocked(false);
            
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => '@',  // Force text format for date to preserve exact format
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function map($row): array
    {
        return [
            $row['account_number'],
            $row['full_name'],
            $row['amount'],
            $row['transaction_date'],
            $row['reference_number'],
            $row['notes']
        ];
    }
    
} 