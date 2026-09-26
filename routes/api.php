<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetItemController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\ProjectTaskController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::apiResource('projects', ProjectController::class);
        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
        Route::patch('/projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
        Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

        Route::get('/projects/{project}/budget-items', [BudgetItemController::class, 'index'])->name('projects.budget-items.index');
        Route::post('/projects/{project}/budget-items', [BudgetItemController::class, 'store'])->name('projects.budget-items.store');
        Route::get('/projects/{project}/budget-items/{budgetItem}', [BudgetItemController::class, 'show'])->name('projects.budget-items.show');
        Route::patch('/projects/{project}/budget-items/{budgetItem}', [BudgetItemController::class, 'update'])->name('projects.budget-items.update');
        Route::delete('/projects/{project}/budget-items/{budgetItem}', [BudgetItemController::class, 'destroy'])->name('projects.budget-items.destroy');

        Route::get('/projects/{project}/tasks', [ProjectTaskController::class, 'index'])->name('projects.tasks.index');
        Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('projects.tasks.store');
        Route::post('/projects/{project}/tasks/bulk', [ProjectTaskController::class, 'bulkStore'])->name('projects.tasks.bulk');
        Route::get('/tasks/{task}', [ProjectTaskController::class, 'show'])->name('tasks.show');
        Route::patch('/tasks/{task}', [ProjectTaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('tasks.destroy');

        Route::get('/projects/{project}/attachments', [AttachmentController::class, 'index'])->name('projects.attachments.index');
        Route::post('/projects/{project}/attachments', [AttachmentController::class, 'store'])->name('projects.attachments.store');
        Route::patch('/attachments/{attachment}', [AttachmentController::class, 'update'])->name('attachments.update');
        Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

        Route::apiResource('users', UserController::class);
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
});
