<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('new_value');
            $table->string('ip_address', 64)->nullable()->after('metadata');
            $table->string('http_method', 12)->nullable()->after('ip_address');
            $table->string('request_path', 512)->nullable()->after('http_method');
            $table->text('user_agent')->nullable()->after('request_path');
        });
    }

    public function down(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->dropColumn(['metadata', 'ip_address', 'http_method', 'request_path', 'user_agent']);
        });
    }
};
