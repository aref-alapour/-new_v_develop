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
        // 1. جدول شهرها (ez_cities)
        Schema::create('ez_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['name']);
        });

        // 2. جدول مناطق (ez_areas)
        Schema::create('ez_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id');
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('city_id')->references('id')->on('ez_cities')->onDelete('cascade');
            $table->fullText(['name']);
        });

        // 3. جدول برندها (ez_brands)
        Schema::create('ez_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['name']);
        });

        // 4. جدول ژانرها (ez_genres)
        Schema::create('ez_genres', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('url')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title']);
        });

        // 5. جدول محصولات (ez_products)
        Schema::create('ez_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->unique();
            $table->string('title')->index();
            $table->unsignedBigInteger('city_id');
            $table->string('city_name')->index();
            $table->string('city_slug')->index();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('area_name')->nullable()->index();
            $table->string('area_slug')->nullable()->index();
            $table->string('hood_name')->nullable()->index();
            $table->unsignedBigInteger('brand_id');
            $table->string('brand_name')->index();
            $table->string('brand_slug')->nullable()->index();
            $table->string('game_type')->index();
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->json('meta')->nullable(); // price, duration, age_limit, level, geo, comments_count, rate, reservation_count, busyness_score, acf_level, zone_escape, etc.
            $table->json('schedule')->nullable(); // ساختار سانس‌های پایه (normals, holidays, times با price/off_price)
            $table->enum('active', ['active', 'deactivated'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')->references('id')->on('ez_cities')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('ez_areas')->onDelete('set null');
            $table->foreign('brand_id')->references('id')->on('ez_brands')->onDelete('cascade');

            $table->index(['city_id', 'game_type', 'area_id']);
            $table->index(['hood_name', 'game_type']);
            $table->index(['brand_id', 'game_type']);
            $table->fullText(['title', 'city_name', 'area_name', 'hood_name', 'brand_name']);
        });

        // 6. Pivot برای ژانرها (ez_product_genres)
        Schema::create('ez_product_genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('genre_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->foreign('genre_id')->references('id')->on('ez_genres')->onDelete('cascade');
            $table->unique(['product_id', 'genre_id']);
        });

        // 7. جدول استثناهای سانس (ez_session_exceptions)
        Schema::create('ez_session_exceptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['closed', 'booked'])->index();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('ez_orders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('set null');

            $table->unique(['product_id', 'date', 'start_time']);
            $table->index(['product_id', 'date', 'status']);
        });

        // 8. جدول سفارشات (ez_orders)
        Schema::create('ez_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_order_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('session_exception_id')->nullable();
            $table->string('status')->index();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('order_date')->index();
            $table->json('items')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->foreign('session_exception_id')->references('id')->on('ez_session_exceptions')->onDelete('set null');
            $table->index(['user_id', 'status', 'order_date']);
        });

        // 9. جدول کاربران (ez_users)
        Schema::create('ez_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_user_id')->unique();
            $table->string('phone')->unique()->index();
            $table->string('firstname')->nullable()->index();
            $table->string('lastname')->nullable()->index();
            $table->string('avatar')->nullable();
            $table->string('utm_source')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['firstname', 'lastname']);
            $table->fullText(['firstname', 'lastname']);
        });

        // 10. Relations: محصولات مدیریت‌شده توسط کاربر (ez_user_game_managers)
        Schema::create('ez_user_game_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->string('role')->default('manager');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->unique(['user_id', 'product_id']);
        });

        // 11. Relations: محصولات مالکیت‌شده توسط کاربر (ez_user_owned_games)
        Schema::create('ez_user_owned_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->unique(['user_id', 'product_id']);
        });

        // 12. Relations: برندهای مالکیت‌شده توسط کاربر (ez_user_owned_brands)
        Schema::create('ez_user_owned_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('brand_id');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('ez_brands')->onDelete('cascade');
            $table->unique(['user_id', 'brand_id']);
        });

        // 13. جدول گزارشات مالی (ez_financial_reports)
        Schema::create('ez_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('type')->index();
            $table->string('description')->nullable();
            $table->timestamp('report_date')->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('ez_orders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->index(['type', 'report_date']);
        });

        // 14. جدول لاگ محصولات (ez_product_logs)
        Schema::create('ez_product_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action')->index();
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('set null');
            $table->index(['product_id', 'action']);
        });

        // 15. جدول کامنت‌های محصول (ez_product_comments)
        Schema::create('ez_product_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('content');
            $table->integer('rating')->default(0);
            $table->string('satisfaction_level')->nullable();
            $table->boolean('approved')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('ez_products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('ez_product_comments')->onDelete('set null');
            $table->index(['product_id', 'approved', 'created_at']);
            $table->fullText(['content']);
        });

        // 16. جدول تراکنش‌های کیف پول (ez_wallet_transactions)
        Schema::create('ez_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('type')->index();
            $table->string('status')->default('pending');
            $table->string('gateway')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->index(['user_id', 'type', 'created_at']);
        });

        // 17. جدول لاگ امتیازات کاربران (ez_user_points_logs)
        Schema::create('ez_user_points_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('points');
            $table->string('reason')->index();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ez_users')->onDelete('cascade');
            $table->index(['user_id', 'reason']);
        });

        // 18. جدول jobs برای Laravel Queue
        Schema::create('ez_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // 19. جدول failed_jobs
        Schema::create('ez_failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 20. جدول سشن‌ها (ez_sessions)
        Schema::create('ez_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 21. جدول نوتیفیکیشن‌ها (ez_notifications)
        Schema::create('ez_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ez_notifications');
        Schema::dropIfExists('ez_sessions');
        Schema::dropIfExists('ez_failed_jobs');
        Schema::dropIfExists('ez_jobs');
        Schema::dropIfExists('ez_user_points_logs');
        Schema::dropIfExists('ez_wallet_transactions');
        Schema::dropIfExists('ez_product_comments');
        Schema::dropIfExists('ez_product_logs');
        Schema::dropIfExists('ez_financial_reports');
        Schema::dropIfExists('ez_user_owned_brands');
        Schema::dropIfExists('ez_user_owned_games');
        Schema::dropIfExists('ez_user_game_managers');
        Schema::dropIfExists('ez_users');
        Schema::dropIfExists('ez_orders');
        Schema::dropIfExists('ez_session_exceptions');
        Schema::dropIfExists('ez_product_genres');
        Schema::dropIfExists('ez_products');
        Schema::dropIfExists('ez_genres');
        Schema::dropIfExists('ez_brands');
        Schema::dropIfExists('ez_areas');
        Schema::dropIfExists('ez_cities');
    }
};