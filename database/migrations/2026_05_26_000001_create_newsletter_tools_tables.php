<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_lang');
            $table->unsignedInteger('id_product');
            $table->string('email');
            $table->string('subject');
            $table->longText('html');
            $table->unsignedInteger('attempts')->default(0);
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['sent', 'attempts']);
            $table->index(['id_product', 'email']);
        });

        Schema::create('newsletter_product_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_product');
            $table->string('reference')->nullable();
            $table->string('brand')->nullable();
            $table->string('decision', 16)->default('sent');
            $table->unsignedBigInteger('operator')->default(0);
            $table->timestamps();

            $table->unique('id_product');
            $table->index('decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_product_decisions');
        Schema::dropIfExists('newsletter_emails');
    }
};
