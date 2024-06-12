<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ResultFileController;
use App\Models\Project;
use App\Models\ProjectFile;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $projects = Project::with('files')->get();
        $totalFilesCount = ProjectFile::count();
        return view('dashboard', compact('projects', 'totalFilesCount'));
    })->name('dashboard');
});

Route::resource('projects', ProjectController::class);
Route::resource('projects.files', ProjectFileController::class)->shallow();
Route::resource('projects.resultFiles', ResultFileController::class)->shallow();
Route::get('/preview-excel/{filename}', [ProjectFileController::class, 'preview'])->name('excel.preview');
Route::get('/download/{filename}', [ProjectFileController::class, 'download'])->name('download');
Route::get('/preview-result/{filename}', [ResultFileController::class, 'preview'])->name('result.preview');
Route::get('/download-result/{filename}', [ResultFileController::class, 'download'])->name('result_download');
Route::post('/projects/{project}/files/sync', [ProjectFileController::class, 'syncAll'])->name('projects.files.syncAll');
Route::post('/files/toggle-enabled', [ProjectFileController::class, 'toggleEnabled']);
