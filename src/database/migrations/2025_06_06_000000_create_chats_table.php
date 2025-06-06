<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // チャットの所有者
            $table->unsignedBigInteger('item_id'); // 商品ID
            $table->text('comment')->nullable(); // チャットでのコメント
            $table->string('image_path')->nullable(); // 画像パス
            $table->tinyInteger('rating')->nullable(); // 1〜5の評価
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
