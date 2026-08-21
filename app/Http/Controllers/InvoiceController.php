<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search;
        $status = $request->status;

        $invoices = Invoice::with(['customer', 'medicalRecord.pet', 'creator'])
            ->when($search, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => [
                'search' => $search,
                'status' => $status,
            ],
            'statuses' => [
                'unpaid'    => 'Chờ thanh toán',
                'paid'      => 'Đã thanh toán',
                'cancelled' => 'Đã hủy',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'customer',
            'medicalRecord.pet',
            'medicalRecord.doctor',
            'medicalRecord.prescriptions.medicine',
            'medicalRecord.services',
            'creator',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Xác nhận thanh toán hóa đơn.
     */
    public function pay(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Hóa đơn này đã được thanh toán trước đó!');
        }

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', "Đã xác nhận thanh toán thành công cho hóa đơn {$invoice->invoice_number}!");
    }

    /**
     * Xuất hóa đơn ra file PDF.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'medicalRecord.pet',
            'medicalRecord.doctor',
            'medicalRecord.prescriptions.medicine',
            'medicalRecord.services',
            'creator',
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a4', 'portrait');

        $filename = "Hoa-don-{$invoice->invoice_number}.pdf";

        return $pdf->stream($filename);
    }
}
