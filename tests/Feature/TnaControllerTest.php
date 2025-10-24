<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Services\TnaService;
use Illuminate\Support\Facades\App;

class TnaControllerTest extends TestCase
{
  
   public function test_it_returns_error_if_employee_inactive_or_not_found()
    {
        $this->withoutMiddleware(); // disable auth & validation

        // Mock service
        $mockService = Mockery::mock(TnaService::class);
        $mockService->shouldReceive('toCheckUserStatusTaskNo')
            ->once()
            ->with('6722', '1233474')
            ->andReturn(false);

        App::instance(TnaService::class, $mockService);

        // Send HTTP request
        $response = $this->postJson('/test-tna', [
            'employeecode' => '6722',
            'jobcode' => '1233474',
            'source' => 'APP',
        ]);

        // Assert JSON response
        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => 'The specified employee does not exist or is currently inactive in the Depot Repair system',
                ]);
    }



}
