<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name; // contact person name
    public $pdfContent;

    public function __construct($name, $pdfContent)
    {
        $this->name = $name;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
       return $this->subject('Inspection Report')
            ->view('emails.inspection')
            ->with([
                'name' => $this->name,
            ])
            ->attachData($this->pdfContent, 'InspectionReport.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
