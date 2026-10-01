<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\AdSpendController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\PnlSettingsController;
use App\Http\Controllers\PnlController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdditionalIncomeController;
use App\Http\Controllers\FounderWithdrawalController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ModuleAccessController;

Route::get('/', function () {
    return view('static.home');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('module:ad-spend')->group(function () {
        Route::get('/ad-spend', [AdSpendController::class, 'index'])->name('ad-spend');
        Route::post('/ad-spend', [AdSpendController::class, 'save'])->name('ad-spend.save');
    });

    Route::middleware('module:pnl')->group(function () {
        Route::get('/pnl', [PnlController::class, 'index'])->name('pnl.index');
    });

    Route::middleware('module:notes')->group(function () {
        Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
        Route::put('/notes/{note}/pin', [NoteController::class, 'togglePin'])->name('notes.pin');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    });

    Route::middleware('admin')->group(function () {

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/ad-spend/sheets', [StoreController::class, 'index'])->name('ad-spend.sheets');
        Route::post('/ad-spend/sheets', [StoreController::class, 'store'])->name('ad-spend.sheets.store');
        Route::put('/ad-spend/sheets/{store}', [StoreController::class, 'update'])->name('ad-spend.sheets.update');
        Route::put('/ad-spend/sheets/{store}/toggle', [StoreController::class, 'toggle'])->name('ad-spend.sheets.toggle');

        Route::get('/pnl/settings', [PnlSettingsController::class, 'index'])->name('pnl.settings');
        Route::post('/pnl/settings', [PnlSettingsController::class, 'store'])->name('pnl.settings.store');
        Route::put('/pnl/settings/{rate}', [PnlSettingsController::class, 'update'])->name('pnl.settings.update');
        Route::delete('/pnl/settings/{rate}', [PnlSettingsController::class, 'destroy'])->name('pnl.settings.destroy');

        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        Route::get('/income', [AdditionalIncomeController::class, 'index'])->name('income.index');
        Route::post('/income', [AdditionalIncomeController::class, 'store'])->name('income.store');
        Route::put('/income/{income}', [AdditionalIncomeController::class, 'update'])->name('income.update');
        Route::delete('/income/{income}', [AdditionalIncomeController::class, 'destroy'])->name('income.destroy');

        Route::get('/withdrawals', [FounderWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals', [FounderWithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::put('/withdrawals/{withdrawal}', [FounderWithdrawalController::class, 'update'])->name('withdrawals.update');
        Route::delete('/withdrawals/{withdrawal}', [FounderWithdrawalController::class, 'destroy'])->name('withdrawals.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/access', [ModuleAccessController::class, 'index'])->name('access.index');
        Route::put('/access/{user}', [ModuleAccessController::class, 'update'])->name('access.update');
    });
});