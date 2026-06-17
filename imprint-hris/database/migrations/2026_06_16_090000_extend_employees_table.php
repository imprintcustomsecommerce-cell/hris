<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('position'); // employees.id of manager
            $table->string('photo')->nullable()->after('address');
            $table->unsignedSmallInteger('vacation_credits')->default(15)->after('status');
            $table->unsignedSmallInteger('sick_credits')->default(15)->after('vacation_credits');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['manager_id', 'photo', 'vacation_credits', 'sick_credits']);
        });
    }
};
