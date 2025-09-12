<?php
 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id(); // Primary key for this table
            //$table->bigInteger('employee_id'); // Must match employee.ID type
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Foreign key
            $table->integer('employee_id'); // matches int
            $table->foreign('employee_id')
                ->references('ID')
                ->on('employee')
                ->onDelete('cascade');
                    });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
