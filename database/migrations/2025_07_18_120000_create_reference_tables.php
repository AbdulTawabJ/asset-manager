<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Lookup/reference tables. Each uses a natural string primary key to mirror
     * the original schema (the Eloquent models set $primaryKey accordingly and
     * disable auto-incrementing).
     */
    public function up(): void
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->string('type', 100)->primary();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->string('department', 100)->primary();
        });

        Schema::create('locations', function (Blueprint $table) {
            // Stored as a hierarchical string: Region-Branch-Office-Department
            $table->string('location', 100)->primary();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->string('file_no', 50)->primary();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('department', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('asset_types');
    }
};
