<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_jobs', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();

            $table->string('status')->default('pending');

            $table->string('file_path')->nullable();

            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_jobs');
    }
};
