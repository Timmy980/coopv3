<?php

namespace App\Imports;

use App\Models\Saving;
use App\Models\MemberAccount;
use App\Models\SavingBulkBatch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SavingsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $batchId;
    protected $cooperativeAccountId;
    protected $userId;
    protected $batch;
    protected $totalAmount = 0;

    public function __construct($batchId, $cooperativeAccountId, $userId)
    {
        $this->batchId = $batchId;
        $this->cooperativeAccountId = $cooperativeAccountId;
        $this->userId = $userId;
        $this->batch = SavingBulkBatch::find($batchId);
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find member account
        $memberAccount = MemberAccount::where('account_number', $row['account_number'])->first();
        
        if (!$memberAccount) {
            $this->batch->increment('failed_records');
            return null;
        }

        $this->totalAmount += $row['amount'];
        $this->batch->increment('processed_records');

        return new Saving([
            'amount' => $row['amount'],
            'transaction_date' => $row['transaction_date'] ?? now(),
            'status' => Saving::STATUS_PENDING,
            'source' => Saving::SOURCE_ADMIN_BULK,
            'reference_number' => $row['reference_number'] ?? ('BULK-' . uniqid()),
            'member_account_id' => $memberAccount->id,
            'cooperative_account_id' => $this->cooperativeAccountId,
            'initiated_by_id' => $this->userId,
            'bulk_batch_id' => $this->batchId,
            'notes' => $row['notes'] ?? null
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'account_number' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'nullable|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string'
        ];
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        // Update batch totals
        if ($this->batch) {
            $this->batch->update([
                'total_amount' => $this->totalAmount,
                'status' => 'completed'
            ]);
        }
    }
} 