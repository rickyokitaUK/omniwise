<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChartAnalysisController;

Route::get('/', function () {
    return view('index');
});

// Example: Laravel Sanctum session-based login for Blade pages
 Route::get('/me', [LoginController::class, 'me']);
Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::get('/messages/inbox', [MessageController::class, 'inbox']);
});

Route::get('/upload-form', [ChartAnalysisController::class, 'showForm']);
Route::post('/analyze-images', [ChartAnalysisController::class, 'analyze']);
Route::get('/analyze-link', [ChartAnalysisController::class, 'analyzeLink']);
Route::get('/llm-decision', [ChartAnalysisController::class, 'showLLMDecision']);

Route::get('/auth', function () {
    return view('auth'); // Vue page
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ✅ Home page (renders Home.vue)
Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/login-test', function () {
    return '✅ Laravel route rendering HTML correctly';
});



// Quick server test route (no DB, no auth, no view)
Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel is running properly on SiteGround!',
        'env' => app()->environment(),
        'php_version' => PHP_VERSION,
        'time' => now()->toDateTimeString(),
    ]);
});
