<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ResultFileController;
use App\Http\Controllers\ProjectInventoryController;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectInventory;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $team = Auth::user()->currentTeam;
        $projects = Project::with('files')
            ->where('team_id', $team->id)
            ->get();
        $totalFilesCount = ProjectFile::whereHas('project', function ($query) use ($team) {
            $query->where('team_id', $team->id);
        })->count();

        // Count the total inventories for the current team
        $totalInventorysCount = ProjectInventory::whereHas('project', function ($query) use ($team) {
            $query->where('team_id', $team->id);
        })->count();

        // Calculate the total count
        $totalCount = $totalFilesCount + $totalInventorysCount;
        return view('dashboard', compact('projects', 'totalCount'));
    })->name('dashboard');
});

Route::resource('projects', ProjectController::class);
Route::resource('projects.files', ProjectFileController::class)->shallow();
Route::resource('projects.inventory', ProjectInventoryController::class)->shallow();
Route::resource('projects.resultFiles', ResultFileController::class)->shallow();
Route::get('/preview-excel/{filename}', [ProjectFileController::class, 'preview'])->name('excel.preview');
Route::get('/download/{filename}', [ProjectFileController::class, 'download'])->name('download');
Route::post('/bulk-action/{type}', [ProjectFileController::class, 'bulkAction'])->name('bulk_action');
Route::get('/preview-result/{filename}', [ResultFileController::class, 'preview'])->name('result.preview');
Route::get('/download-result/{filename}', [ResultFileController::class, 'download'])->name('result_download');
Route::post('/projects/{project}/files/sync', [ProjectFileController::class, 'syncAll'])->name('projects.files.syncAll');
Route::post('/projects/{project}/files/manual-sync', [ProjectFileController::class, 'manualSync'])->name('projects.files.manualSync');
Route::post('/files/toggle-enabled', [ProjectFileController::class, 'toggleEnabled']);
Route::get('/preview-inventory/{filename}', [ProjectInventoryController::class, 'preview'])->name('inventory.preview');
Route::get('/download-inventory/{filename}', [ProjectInventoryController::class, 'download'])->name('inventory_download');
