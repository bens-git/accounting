<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['PURCHASE', 'BILL', 'INCOME']);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['PRE-AUTHORIZED FROM BANK', 'AUTO-PAY FROM BANK', 'DIRECT DEPOSIT', 'CREDIT CARD', 'BANK TO BANK TRANSFER'])->nullable();
            $table->string('details')->nullable();
            $table->enum('tag', ['MAINTENANCE'])->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->enum('recurrence_type', ['MONTHLY', 'BI-MONTHLY', 'YEARLY']); // type of recurrence
            $table->timestamp('recurrence_start_month'); // start month of the recurrence
            $table->foreignId('party_id')->nullable()->constrained('parties')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drafts');
    }
}
