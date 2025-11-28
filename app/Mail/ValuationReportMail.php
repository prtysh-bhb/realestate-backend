<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValuationReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $pdfPath;

    public function __construct(array $data, string $pdfPath = null)
    {
        $this->data = $data;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        $email = $this->subject('Property Valuation Report - ' . $this->data['property_address'])
                      ->view('emails.valuation-report');
        
        if ($this->pdfPath) {
            $email->attach($this->pdfPath);
        }
        
        return $email;
    }
}