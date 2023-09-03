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
        Schema::create('entries', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->string('title', 128)->unique()->comment('題名');
            $table->string('subtitle', 256)->nullable()->comment('補題');
            $table->text('body')->comment('本文');
            $table->foreignId('parent_entry_id')->nullable()->comment('親項目ID');
            $table->foreignId('post_user_id')->constrained('users')->comment('投稿ユーザID');
            $table->boolean('is_publish')->default(false)->comment('公開フラグ');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
