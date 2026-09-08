<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table): void {
            $table->string('source', 20)->default('WEBSITE')->after('id')->index();
            $table->string('screenshot_path')->nullable()->after('message');
            $table->timestamp('occurred_at')->nullable()->after('screenshot_path');
            $table->string('email')->nullable()->change();
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feedback_id')->nullable()->unique()->constrained('feedback')->nullOnDelete();
            $table->string('display_name', 120);
            $table->string('customer_type', 120)->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('content');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->boolean('featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->string('question', 240);
            $table->text('answer');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('show_on_home')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');

        DB::table('feedback')->whereNull('email')->update(['email' => '']);
        Schema::table('feedback', function (Blueprint $table): void {
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'screenshot_path', 'occurred_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
