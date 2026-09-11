<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A QR scan no longer opens a table on its own: the session waits until a
 * cashier or receptionist confirms the guest is really seated there, and a
 * friend's phone joins with the 4-digit code shown on the first phone.
 *
 * `status` was `enum('active','closed')`, which Postgres enforces as a CHECK
 * constraint — it has to go before the column can hold `pending` and
 * `rejected`. The values now live on App\Domains\Table\Enums\TableSessionStatus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE table_sessions DROP CONSTRAINT IF EXISTS table_sessions_status_check');
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
            $table->string('join_code', 4)->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('close_reason', 30)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('table_session_id')->nullable()->constrained('table_sessions')->nullOnDelete();
        });

        // Sessions already open before this migration were never given a code,
        // so nobody could join them. Hand them one.
        DB::table('table_sessions')
            ->where('status', 'active')
            ->whereNull('join_code')
            ->orderBy('id')
            ->each(fn (object $row) => DB::table('table_sessions')
                ->where('id', $row->id)
                ->update(['join_code' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT)]));
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_session_id');
        });

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['join_code', 'approved_at', 'close_reason']);
        });

        // The old two-state column has no room for a session the cashier never confirmed.
        DB::table('table_sessions')
            ->whereIn('status', ['pending', 'rejected'])
            ->update(['status' => 'closed']);

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE table_sessions ADD CONSTRAINT table_sessions_status_check CHECK (status IN ('active', 'closed'))");
        }
    }
};
