<?php

namespace App\Interface;

use App\Http\Requests\TnaRequest;

interface TnaTaskHandlerInterface
{
    public function handle(TnaRequest $request);
}
