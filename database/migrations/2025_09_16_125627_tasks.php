<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('assignee')->nullable();
            $table->date('due_date');
            $table->integer('time_tracked')->default(0);
            $table->enum('status',['pending','open','in_progress','completed'])->default('pending');
            $table->enum('priority',['low','medium','high']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
