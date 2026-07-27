<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Faithful reproduction of the original `assets` table. The model
     * (App\Models\Asset) disables Eloquent timestamps and instead relies on the
     * `last_updated_on` column, so no created_at/updated_at columns are added.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 100);
            $table->string('serial', 100)->nullable();
            $table->date('date_of_purchase')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->string('type', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 0)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('owner', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('remarked_by', 150)->nullable();
            $table->boolean('requires_it_remark')->default(false);
            $table->timestamp('last_updated_on')->useCurrent();
            $table->enum('status', ['None', 'Working', 'Damaged'])->default('None');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
