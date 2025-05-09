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
        // Tabel Departments (Jurusan)
        Schema::create('departments', function (Blueprint $table) {
            $table->id('dept_id');
            $table->string('dept_name', 100);
            $table->string('faculty', 100)->nullable();
            $table->timestamps();
        });

        // Tabel Chat Sessions
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('session_id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // Tabel Chat Logs
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->uuid('session_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->enum('source', ['knowledge_base', 'ai_generated']);
            $table->string('knowledge_id', 24)->nullable(); // Untuk referensi ke MongoDB
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('session_id')->references('session_id')->on('chat_sessions')->onDelete('cascade');
        });

        // Tabel Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notif_id');
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->timestamp('publish_date')->useCurrent();
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('is_important')->default(false);
            $table->timestamps();
        });

        // Tabel Events
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->timestamp('event_date');
            $table->string('location', 100)->nullable();
            $table->unsignedBigInteger('dept_id')->nullable();
            $table->timestamps();
            $table->foreign('dept_id')
                ->references('dept_id')
                ->on('departments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel dengan urutan yang benar untuk menghindari error constraint
        Schema::dropIfExists('chat_logs');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('events');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('departments');
    }
};
