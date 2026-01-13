<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TnaRequest;
use App\Services\Tna\AppTaskHandler;
use App\Services\Tna\HighmessageTaskHandler;
use App\Services\Tna\SMSTaskHandler;
use App\Services\TnaService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class TnaEntryController extends Controller
{
  use ApiResponse;
  public $tnaService;


  public function __construct(TnaService  $tnaService)
  {
    $this->tnaService = $tnaService;
  }
  

    public function createOrUpdateTNAEntry(TnaRequest $request)
    {
        try {
            // Check employee status
            $status = $this->tnaService
                ->toCheckUserStatusTaskNo($request->employeecode, $request->jobcode);

            if (!$status) {
                return $this->errorResponse(
                    'The specified employee does not exist or is currently inactive in the Depot Repair system'
                );
            }

            // Check job card
            $task = $this->tnaService->toCheckJobCard($request->jobcode);
            if (!$task) {
                return $this->errorResponse(
                    'Unable to create the task due to an issue with the job card. Please verify the job details and try again'
                );
            }

            // Handlers map
            $handlers = [
                'APP'         => AppTaskHandler::class,
                'Highmessage' => HighmessageTaskHandler::class,
                'SMS'         => SMSTaskHandler::class,
            ];

            // Validate source
            if (!isset($handlers[$request->source])) {
                return $this->errorResponse('Invalid operation to perform');
            }

            // Resolve handler
            $className = $handlers[$request->source];

            if (!class_exists($className)) {
                throw new \Exception("Handler class not found: {$className}");
            }

            // Create handler instance
            $handler = new $className($this->tnaService);

            // Execute handler
            $response = $handler->handle($request);

            return $this->response($response);

        } catch (\Throwable $e) {

            FacadesLog::error('TNA Create/Update Error', [
                'message'      => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE'      => $request->jobcode,
                'SOURCE'       => $request->source,
            ]);

            return $this->errorResponse(
                'An unexpected error occurred. Please try again later.'
            );
        }
    }





}
