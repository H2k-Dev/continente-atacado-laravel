<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // protocolo do orçamento, ex.: ORC-2026-0001
            $table->string('cliente_nome');
            $table->string('empresa')->nullable();
            $table->string('email');
            $table->string('telefone');
            $table->string('cidade')->nullable();
            $table->text('mensagem')->nullable();
            $table->string('status')->default('novo'); // novo, em_andamento, respondido, fechado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
