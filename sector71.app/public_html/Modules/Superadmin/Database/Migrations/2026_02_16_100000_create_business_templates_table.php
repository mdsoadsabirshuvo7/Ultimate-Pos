<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#3c8dbc');
            $table->string('image')->nullable();
            
            // Template Data (stored as JSON for flexibility)
            $table->json('categories')->nullable();
            $table->json('units')->nullable();
            $table->json('tax_rates')->nullable();
            $table->json('products')->nullable();
            $table->json('brands')->nullable();
            $table->json('expense_categories')->nullable();
            $table->json('business_settings')->nullable();
            $table->json('enabled_modules')->nullable();
            
            // Meta
            $table->boolean('is_active')->default(1);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_templates');
    }
};
