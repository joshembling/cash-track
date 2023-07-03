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
        Schema::table('expenses', function (Blueprint $table) {
            $table->dateTime('reminder_sent_user')->after('expense_date')->nullable();
            $table->dateTime('reminder_sent_payee')->after('reminder_sent_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_user');
            $table->dropColumn('reminder_sent_payee');
        });
    }
};
