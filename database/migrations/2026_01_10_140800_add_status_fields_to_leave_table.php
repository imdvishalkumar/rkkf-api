<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_table', function (Blueprint $table) {
            // Status: 0=pending, 1=approved, -1=rejected
            if (!Schema::hasColumn('leave_table', 'status')) {
                $table->tinyInteger('status')->default(0)->after('reason');
            }

            // Tracking fields
            if (!Schema::hasColumn('leave_table', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('leave_table', 'reviewed_by')) {
                $table->integer('reviewed_by')->nullable()->after('applied_at');
            }
            if (!Schema::hasColumn('leave_table', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_table', function (Blueprint $table) {
            $table->dropColumn(['status', 'applied_at', 'reviewed_by', 'reviewed_at']);
        });
    }
};
