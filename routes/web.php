<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'products'])->name('products.index');
Route::get('/products/{product}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/categories', [StorefrontController::class, 'categories'])->name('categories.index');
Route::get('/categories/{category}', [StorefrontController::class, 'category'])->name('categories.show');
Route::view('/about', 'store.about')->name('about');
Route::view('/contact', 'store.contact')->name('contact');
Route::get('/faq', [StorefrontController::class, 'faq'])->name('faq');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::view('/cart', 'store.cart')->name('cart');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'customerForm'])->name('login');
    Route::post('/login', [AuthController::class, 'customerLogin'])->middleware('throttle:8,15')->name('login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,15')->name('register.store');
    Route::get('/admin/login', [AuthController::class, 'adminForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:5,15')->name('admin.login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:CUSTOMER'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/feedback', [OrderController::class, 'feedback'])->name('orders.feedback');
});

Route::post('/analytics/view', [AnalyticsController::class, 'store'])->middleware('throttle:30,1')->name('analytics.view');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('products', AdminProductController::class)->except('show');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'status'])->name('orders.status');
    Route::post('/orders/{order}/items', [AdminOrderController::class, 'addItem'])->name('orders.items.store');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{order}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/create', [AdminFeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [AdminFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{feedback}/edit', [AdminFeedbackController::class, 'edit'])->name('feedback.edit');
    Route::put('/feedback/{feedback}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
    Route::get('/feedback/{feedback}/screenshot', [AdminFeedbackController::class, 'screenshot'])->name('feedback.screenshot');
    Route::resource('testimonials', AdminTestimonialController::class)->except('show');
    Route::resource('faqs', AdminFaqController::class)->except('show');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/orders', [AdminReportController::class, 'orders'])->name('reports.orders');
    Route::get('/reports/products', [AdminReportController::class, 'products'])->name('reports.products');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings', [AdminSettingsController::class, 'reset'])->name('settings.reset');
});
