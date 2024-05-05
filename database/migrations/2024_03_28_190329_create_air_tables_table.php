<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('air_tables', function (Blueprint $table) {
            $table->id();
            $table->string('base');
            $table->string('table');
            $table->string('record');
            $table->string('field');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_tables');
    }
};
