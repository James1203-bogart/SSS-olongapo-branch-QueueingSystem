<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('number');
                $table->string('priority');
                $table->string('category');
                $table->string('category_id')->nullable();
                $table->string('mode');
                $table->string('status');
                $table->string('counter')->nullable();
                $table->string('branch')->nullable();
                $table->timestamp('called_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                // Indexes for faster fetching
                $table->index(['branch']);
                $table->index(['status']);
                $table->index(['counter']);
                $table->index(['category_id']);
                $table->index(['created_at']);
                $table->index(['branch', 'status']);
                $table->index(['branch', 'status', 'counter']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
