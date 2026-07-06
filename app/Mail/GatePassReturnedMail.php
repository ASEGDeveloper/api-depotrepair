<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GatePassReturnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $gatePass;
    public $items;

    public function __construct($gatePass, $items)
    {
        $this->gatePass = $gatePass;
        $this->items    = $items;
    }

    public function build()
    {
        return $this->from('depot@ase.ae', 'Depotrepair')
            ->subject('Gate Pass Closed (Return Verified) - ' . $this->gatePass->gate_pass_no)
            ->view('emails.gate_pass_returned');
    }
}
