<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectInventory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\ResultFile;
use Illuminate\Support\Facades\Storage;

class ProjectInventoryController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);
        Log::debug("$request this is request ");
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $extension = $file->getClientOriginalExtension();
            $originalFileName = $file->getClientOriginalName();
            $fileName = 'projects_' . time() . '_' . Str::random(10) . '.' . $extension;
            $file->storeAs(ProjectInventory::$FOLDER_PATH, $fileName);

            $project->inventories()->create([
                'file' => $fileName,
                'original_name' => $originalFileName,
            ]);
        }
        
        return response()->json(['status' => 'Files uploaded successfully']);
    }

    public function preview($filename)
    {
        $filePath = storage_path('app/uploads/project_inventories/' . $filename);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
        } catch (\Exception $e) {
            // Handle the exception if the file cannot be read
            return response()->json(['error' => 'Unable to read the file.'], 500);
        }

        // Render the HTML content of the preview
        $htmlContent = view('files.preview', compact('data'))->render();

        return response()->json(['html' => $htmlContent]);
    }

    public function download($filename)
    {
        $projectinventory = ProjectInventory::find($filename);
        $path = storage_path('app/uploads/project_inventories/' . $projectinventory->file);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response()->download($path, $projectinventory->original_name, ['Content-Type' => $type]);
    }

    public function destroy(ProjectInventory $inventory)
    {
        $inventory->delete();
        return redirect()->back();
    }
}
