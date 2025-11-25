<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF invoice
     */
    public function generateInvoice(Payment $payment)
    {
        $payment->load(['user', 'subscription.plan']);

        $pdf = Pdf::loadView('invoices.payment-invoice', [
            'payment' => $payment,
            'subscription' => $payment->subscription,
        ]);

        return $pdf;
    }

    /**
     * Generate and save invoice
     */
    public function generateAndSaveInvoice(Payment $payment)
    {
        $pdf = $this->generateInvoice($payment);
        
        $filename = 'invoice_' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '.pdf';
        $path = 'invoices/' . $filename;

        // Save to storage
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Email invoice to customer
     */
    public function emailInvoice(Payment $payment)
    {
        $pdf = $this->generateInvoice($payment);
        $filename = 'invoice_' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        try {
            Mail::send('emails.invoice', [
                'payment' => $payment,
                'subscription' => $payment->subscription,
            ], function ($message) use ($payment, $pdf, $filename) {
                $message->to($payment->user->email, $payment->user->name)
                    ->subject('Invoice for Your Subscription - ' . $payment->subscription->plan->name)
                    ->attachData($pdf->output(), $filename, [
                        'mime' => 'application/pdf',
                    ]);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email: ' . $e->getMessage());
            return false;
        }
    }
}