<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('number');
            $table->string('priority');
            $table->string('category');
            $table->string('category_id')->nullable();
            $table->string('mode')->nullable();
            $table->string('status')->index(); // waiting | serving | completed
            $table->string('counter')->nullable()->index();
            $table->timestamp('called_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->string('branch')->nullable()->index();
            $table->timestamps();

            $table->index(['branch','category_id']);
            $table->index(['branch','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
