<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First create discounts table
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->integer('usage_limit')->nullable();
            $table->integer('times_used')->default(0);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Then modify rentals table
        Schema::table('rentals', function (Blueprint $table) {
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('late_fees', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->foreignId('discount_id')->nullable()->constrained();
        });

        // Finally create payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('late_fees', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropColumn(['security_deposit', 'late_fees', 'discount_amount', 'discount_id']);
        });
        Schema::dropIfExists('discounts');
    }
};