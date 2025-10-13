<?php

namespace App\Services\Tna;

use App\Http\Requests\TnaRequest;
use App\Interface\TnaTaskHandlerInterface;

class SMSTaskHandler implements TnaTaskHandlerInterface
{
    protected $tnaService;

    public function __construct($tnaService)
    {
        $this->tnaService = $tnaService;
    }

    public function handle(TnaRequest $request)
    {
        return $this->tnaService->updateSMSTask($request);
    }
}
