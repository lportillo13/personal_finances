<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MonthLockController;
use App\Http\Controllers\PayPeriodController;
use App\Http\Controllers\RecurringRuleController;
use App\Http\Controllers\ScheduledItemStatusController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('health');

Route::get('/up', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('up');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::post('/accounts/{account}/set-funding', [AccountController::class, 'setFunding'])
        ->name('accounts.setFunding');
    Route::get('/credit-cards/{account}', [CreditCardController::class, 'show'])
        ->whereNumber('account')
        ->name('credit-cards.show');
    Route::get('/credit-cards/{account}/pay', [CreditCardController::class, 'payForm'])
        ->whereNumber('account')
        ->name('credit-cards.payForm');
    Route::post('/credit-cards/{account}/pay', [CreditCardController::class, 'pay'])
        ->whereNumber('account')
        ->name('credit-cards.pay');
    Route::post('/credit-cards/{account}/create-statement-payment', [CreditCardController::class, 'createStatementPayment'])
        ->whereNumber('account')
        ->name('credit-cards.createStatementPayment');
    Route::resource('credit-cards', CreditCardController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('recurring-rules', RecurringRuleController::class)->except(['show']);

    Route::get('/pay-periods', [PayPeriodController::class, 'index'])->name('pay-periods.index');
    Route::post('/pay-periods/allocate', [PayPeriodController::class, 'allocate'])->name('pay-periods.allocate');
    Route::post('/pay-periods/{income}/savings', [PayPeriodController::class, 'saveSavings'])->name('pay-periods.savings');
    Route::post('/allocations/reassign', [AllocationController::class, 'reassign'])->name('allocations.reassign');
    Route::post('/allocations/split', [AllocationController::class, 'split'])->name('allocations.split');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/day', [CalendarController::class, 'day'])->name('calendar.day');

    Route::post('/scheduled-items/{scheduledItem}/mark-paid', [ScheduledItemStatusController::class, 'markPaid'])->name('scheduled-items.markPaid');
    Route::post('/scheduled-items/{scheduledItem}/mark-skipped', [ScheduledItemStatusController::class, 'markSkipped'])->name('scheduled-items.markSkipped');
    Route::post('/scheduled-items/{scheduledItem}/mark-pending', [ScheduledItemStatusController::class, 'markPending'])->name('scheduled-items.markPending');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/reconcile', [TransactionController::class, 'bulkReconcile'])->name('transactions.reconcile');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/commit', [ImportController::class, 'commit'])->name('import.commit');

    Route::get('/settings/locks', [MonthLockController::class, 'index'])->name('locks.index');
    Route::post('/settings/locks', [MonthLockController::class, 'store'])->name('locks.store');
    Route::post('/settings/locks/lock-last', [MonthLockController::class, 'lockLast'])->name('locks.lockLast');
    Route::delete('/settings/locks/{lock}', [MonthLockController::class, 'destroy'])->name('locks.destroy');

    Route::post('/schedule/generate', [ScheduleController::class, 'generate'])->name('schedule.generate');
});
