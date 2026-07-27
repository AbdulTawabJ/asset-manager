<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records every "shift" of an asset (change of owner/location) and whether
     * it still needs an IT remark. Model disables Eloquent timestamps and uses
     * the `date` column instead.
     */
    public function up(): void
    {
        Schema::create('asset_history', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 100);
            $table->text('description')->nullable();
            $table->string('prev_location', 100)->nullable();
            $table->string('new_location', 100)->nullable();
            $table->string('prev_owner', 50)->nullable();
            $table->string('new_owner', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('remarked_by', 150)->nullable();
            $table->boolean('requires_it_remark')->default(false);
            $table->timestamp('date')->useCurrent();
            $table->enum('status', ['None', 'Working', 'Damaged'])->default('None');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_history');
    }
};
