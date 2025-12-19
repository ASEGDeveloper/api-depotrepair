<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;  
    public $surveyType;
    public $surveyDate;
    public $itemNumber;
    public $pdfContent;

    public function __construct($name,$surveyType, $surveyDate, $itemNumber, $pdfContent)
    {
        $this->name = $name;
        $this->surveyType = $surveyType;
        $this->surveyDate = $surveyDate;
        $this->itemNumber = $itemNumber;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
       return $this->subject('Inspection Report')
            ->view('emails.inspection')
            ->with([
                'name' => $this->name,
                'surveyType' => $this->surveyType,
                'surveyDate' => $this->surveyDate,
                'itemNumber' => $this->itemNumber,
            ])
            ->attachData($this->pdfContent, 'InspectionReport.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
