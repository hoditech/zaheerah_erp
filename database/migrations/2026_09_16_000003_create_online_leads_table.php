<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bulk-quote requests and contact-form messages sent from the public
     * storefront land here so staff can work them inside the ERP.
     */
    public function up(): void
    {
        Schema::create('online_leads', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('general'); // quote | contact | general
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();        // raw cart / form details
            $table->string('source')->default('storefront');
            $table->string('external_ref')->nullable()->unique();
            $table->string('status')->default('new');   // new | contacted | archived
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_leads');
    }
};
