<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('erp_source')->nullable()->after('id');
            $table->string('erp_external_id')->nullable()->after('erp_source');
            $table->timestamp('synced_at')->nullable()->after('ativo');

            $table->unique(['erp_source', 'erp_external_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('erp_source')->nullable()->after('id');
            $table->string('erp_external_id')->nullable()->after('erp_source');
            $table->string('codigo')->nullable()->after('slug');
            $table->decimal('preco', 12, 2)->nullable()->after('marca');
            $table->timestamp('synced_at')->nullable()->after('ordem');

            $table->unique(['erp_source', 'erp_external_id']);
            $table->index('codigo');
        });

        Schema::create('erp_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('erp_source');
            $table->string('status'); // running, success, failed
            $table->unsignedInteger('categories_created')->default(0);
            $table->unsignedInteger('categories_updated')->default(0);
            $table->unsignedInteger('products_created')->default(0);
            $table->unsignedInteger('products_updated')->default(0);
            $table->unsignedInteger('products_deactivated')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_sync_logs');

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['erp_source', 'erp_external_id']);
            $table->dropIndex(['codigo']);
            $table->dropColumn(['erp_source', 'erp_external_id', 'codigo', 'preco', 'synced_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['erp_source', 'erp_external_id']);
            $table->dropColumn(['erp_source', 'erp_external_id', 'synced_at']);
        });
    }
};