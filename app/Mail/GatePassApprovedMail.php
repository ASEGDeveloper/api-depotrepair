<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GatePassApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $gatePass;
    public $items;
    public $comment;

    public function __construct($gatePass, $items, $comment)
    {
        $this->gatePass = $gatePass;
        $this->items    = $items;
        $this->comment  = $comment;
    }

    public function build()
    {
        return $this->from('depot@ase.ae', 'Depotrepair')
            ->subject('Gate Pass Approved - ' . $this->gatePass->gate_pass_no)
            ->view('emails.gate_pass_approved');
    }
}
