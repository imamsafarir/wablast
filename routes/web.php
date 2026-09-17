<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [WaController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Kelola User (Superadmin & Admin)
    Route::resource('users', UserController::class)->middleware('admin')->except(['create', 'show', 'edit']);

    // Route WhatsApp Blast & Setting
    Route::get('/wa/setting', [WaController::class, 'setting'])->name('wa.setting');
    Route::post('/wa/setting/instance', [WaController::class, 'updateUserInstance'])->name('wa.setting.instance');
    Route::post('/wa/setting/branding', [WaController::class, 'updateBranding'])->name('wa.setting.branding');
    Route::post('/wa/setting/evolution', [WaController::class, 'updateEvolutionConfig'])->name('wa.setting.evolution');
    Route::post('/wa/setting/test-evolution', [WaController::class, 'testEvolution'])->name('wa.setting.test-evolution');
    Route::post('/wa/disconnect', [WaController::class, 'disconnect'])->name('wa.disconnect');

    Route::get('/wa/blast', [WaController::class, 'blast'])->name('wa.blast');
    Route::get('/wa/account-groups', [WaController::class, 'fetchAccountWaGroups'])->name('wa.account.groups');
    Route::post('/wa/group-participants', [WaController::class, 'fetchGroupParticipants'])->name('wa.group.participants');

    // API untuk Progress Bar (Tanpa refresh)
    Route::post('/wa/send-single', [WaController::class, 'sendSingle'])->name('wa.send.single');

    // Route Template Pesan
    Route::post('/wa/template', [WaController::class, 'storeTemplate'])->name('wa.template.store');
    Route::put('/wa/template/{id}', [WaController::class, 'updateTemplate'])->name('wa.template.update');
    Route::delete('/wa/template/{id}', [WaController::class, 'deleteTemplate'])->name('wa.template.delete');

    // Route Grup Kontak
    Route::post('/wa/contact-group', [WaController::class, 'storeContactGroup'])->name('wa.contact.store');
    Route::put('/wa/contact-group/{id}', [WaController::class, 'updateContactGroup'])->name('wa.contact.update');
    Route::delete('/wa/contact-group/{id}', [WaController::class, 'deleteContactGroup'])->name('wa.contact.delete');

    // Route API Progress Bar & Background Blast
    Route::post('/wa/blast/start', [WaController::class, 'startBlast'])->name('wa.blast.start');
    Route::get('/wa/blast/active', [WaController::class, 'getActiveBlast'])->name('wa.blast.active');
    Route::get('/wa/blast/campaign/{id}/status', [WaController::class, 'getBlastProgress'])->name('wa.blast.status');
    Route::get('/wa/blast/campaign/{id}', [WaController::class, 'showCampaign'])->name('wa.blast.campaign.show');
    Route::get('/wa/blast/campaign/{id}/failed-recipients', [WaController::class, 'getFailedRecipients'])->name('wa.blast.campaign.failed');
    Route::post('/wa/blast/campaign/{id}/retry', [WaController::class, 'retryFailedCampaign'])->name('wa.blast.campaign.retry');

    // Route Log Aktivitas Sistem
    Route::get('/wa/logs', [WaController::class, 'logs'])->name('wa.logs');
});

require __DIR__.'/auth.php';
