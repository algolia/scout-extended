<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateEmptyItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('empty_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empty_items');
    }
}
