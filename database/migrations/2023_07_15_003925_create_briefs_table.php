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
        Schema::create('briefs', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->string('title', 128)->unique()->comment('タイトル');
            $table->string('note', 256)->nullable()->comment('補足');
            $table->text('abstract')->comment('概要');
            $table->text('hands_on')->nullable()->comment('ハンズオン');
            $table->foreignId('parent_brief_id')->nullable()->comment('親記事ID');
            $table->foreignId('entry_user_id')->constrained('users')->comment('投稿ユーザID');
            $table->dateTime('entry_at')->comment('投稿日時');
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
        Schema::dropIfExists('briefs');
    }
};
