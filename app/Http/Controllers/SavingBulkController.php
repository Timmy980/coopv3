<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\SavingBulkBatch;
use App\Models\MemberAccount;
use App\Models\CooperativeAccount;
use App\Models\AccountType;
use App\Imports\SavingsImport;
use App\Exports\SavingsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SavingBulkController extends Controller
{
    /**
     * Show the bulk upload form
     */
    public function showUploadForm()
    {
        return Inertia::render('Savings/BulkUpload', [
            'cooperative_accounts' => CooperativeAccount::active()->get(),
            'account_types' => AccountType::all()
        ]);
    }

    /**
     * Process bulk upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id',
            'account_type_id' => 'required|exists:account_types,id'
        ]);

        // Create bulk batch record
        $batch = SavingBulkBatch::create([
            'batch_reference' => 'BULK-' . Str::random(10),
            'status' => 'processing',
            'created_by_id' => $request->user()->id,
            'total_records' => 0,
            'processed_records' => 0,
            'failed_records' => 0,
            'total_amount' => 0,
            'account_type_id' => $request->account_type_id
        ]);

        // Process Excel file
        Excel::import(new SavingsImport(
            $batch->id,
            $request->cooperative_account_id,
            $request->user()->id,
            $request->account_type_id
        ), $request->file('file'));

        return redirect()->route('savings.bulk.show', $batch->id);
    }

    /**
     * Display list of bulk batches
     */
    public function index()
    {
        $batches = SavingBulkBatch::with('createdBy')
            ->latest()
            ->paginate(15);

        return Inertia::render('Savings/BulkBatches', [
            'batches' => $batches
        ]);
    }

    /**
     * Display specific bulk batch
     */
    public function show(SavingBulkBatch $batch)
    {
        $batch->load('createdBy');
        
        $savings = Saving::where('bulk_batch_id', $batch->id)
            ->with(['memberAccount', 'cooperativeAccount'])
            ->paginate(15);

        return Inertia::render('Savings/BulkBatchShow', [
            'batch' => $batch,
            'savings' => $savings
        ]);
    }

    /**
     * Download bulk upload template
     */
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'account_type_id' => 'required|exists:account_types,id'
        ]);

        $filename = 'savings_template_' . strtolower(str_replace(' ', '_', AccountType::find($request->account_type_id)->name)) . '.xlsx';
        
        return Excel::download(
            new SavingsTemplateExport($request->account_type_id), 
            $filename
        );
    }
} 