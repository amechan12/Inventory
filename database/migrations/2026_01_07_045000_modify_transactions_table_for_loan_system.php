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
        Schema::table('transactions', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('transactions', 'status')) {
                // Ubah payment_method menjadi nullable karena ini sistem pinjaman (jika belum nullable)
                if (Schema::hasColumn('transactions', 'payment_method')) {
                    $table->enum('payment_method', ['cash', 'qris', 'debit'])->nullable()->change();
                }
                if (Schema::hasColumn('transactions', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->unsigned()->nullable()->change();
                }
                
                // Tambah field untuk sistem pinjaman
                $table->enum('status', ['pending', 'borrowed', 'returning', 'returned', 'cancelled'])->default('pending')->after('user_id');
                $table->text('borrow_reason')->nullable()->after('status');
                $table->unsignedInteger('duration')->comment('Durasi pinjaman dalam hari')->nullable()->after('borrow_reason');
                $table->date('borrow_date')->nullable()->after('duration');
                $table->date('return_date')->nullable()->after('borrow_date');
                $table->enum('condition_on_return', ['good', 'damaged', 'lost'])->nullable()->after('return_date');
                $table->text('return_notes')->nullable()->after('condition_on_return');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('return_notes');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop foreign key if exists
            if (Schema::hasColumn('transactions', 'approved_by')) {
                try {
                    $table->dropForeign(['approved_by']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
            }
            
            // Drop columns if they exist
            $columnsToDrop = [
                'status',
                'borrow_reason',
                'duration',
                'borrow_date',
                'return_date',
                'condition_on_return',
                'return_notes',
                'approved_by',
                'approved_at'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Revert nullable changes if columns exist
            if (Schema::hasColumn('transactions', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'qris', 'debit'])->nullable(false)->change();
            }
            if (Schema::hasColumn('transactions', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->unsigned()->nullable(false)->change();
            }
        });
    }
};
