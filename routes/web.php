<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PublicNoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NoteReactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupportController;

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.post');

    Route::get('/forgot-password', [PasswordResetController::class, 'createLinkRequest'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'createPasswordReset'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('password.update');

});

Route::middleware(['auth', 'active'])->group(function () {

    Route::post(
        '/public-notes/{note}/comments',
        [CommentController::class, 'store']
    )->name('comments.store');

    Route::post(
        '/public-notes/{note}/reactions',
        [NoteReactionController::class, 'store']
    )->name('note-reactions.store');

    Route::put(
        '/comments/{comment}',
        [CommentController::class, 'update']
    )->name('comments.update');

    Route::delete(
        '/comments/{comment}',
        [CommentController::class, 'destroy']
    )->name('comments.destroy');

});

Route::get('/public-notes', [PublicNoteController::class, 'index'])
    ->name('public-notes.index');

Route::get('/public-notes/{note}', [PublicNoteController::class, 'show'])
    ->name('public-notes.show');

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'dismiss'])
        ->name('notifications.dismiss');

    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/support/messages', [SupportController::class, 'index'])
        ->name('support.index');
    Route::get('/support/messages/create', [SupportController::class, 'create'])
        ->name('support.create');
    Route::post('/support/messages', [SupportController::class, 'store'])
        ->name('support.store');
    Route::get('/support/messages/{conversation}', [SupportController::class, 'show'])
        ->name('support.show');
    Route::post('/support/messages/{conversation}', [SupportController::class, 'reply'])
        ->name('support.reply');

    Route::redirect('/', '/notes');

    Route::get('/notes-export', [NoteController::class, 'export'])
    ->name('notes.export');

    Route::resource('notes', NoteController::class);

});

Route::middleware(['auth', 'active', 'can:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::post('/users/{user}/password-reset', [AdminController::class, 'sendPasswordResetLink'])
            ->name('users.password-reset');
        Route::put('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::patch('/users/{user}/status', [AdminController::class, 'toggleUser'])->name('users.status');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/content', [AdminController::class, 'content'])->name('content.index');
        Route::get('/public-notes', [AdminController::class, 'publicNotes'])->name('public-notes.index');
        Route::delete('/notes/{note}', [AdminController::class, 'destroyNote'])->name('notes.destroy');
        Route::delete('/comments/{comment}', [AdminController::class, 'destroyComment'])->name('comments.destroy');

        Route::get('/support', [SupportController::class, 'adminIndex'])->name('support.index');
        Route::get('/support/{conversation}', [SupportController::class, 'adminShow'])->name('support.show');
        Route::post('/support/{conversation}', [SupportController::class, 'adminReply'])->name('support.reply');
    });