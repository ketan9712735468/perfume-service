<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\ResultFile;
use Illuminate\Support\Facades\Storage;


class ProjectFileController extends Controller
{
    public function index(Project $project)
    {
        $files = $project->files;
        return view('files.index', compact('project', 'files'));
    }

    public function create(Project $project)
    {
        // dd($project->files);

        return view('files.create', compact('project'));
    }

    public function download($filename)
    {
        $path = storage_path('app/uploads/projects/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response()->download($path, $filename, ['Content-Type' => $type]);
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);
        Log::debug("$request this is request ");
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $extension = $file->getClientOriginalExtension();
            $fileName = 'projects_' . time() . '_' . Str::random(10) . '.' . $extension;
            $file->storeAs(ProjectFile::$FOLDER_PATH, $fileName);

            $project->files()->create([
                'file' => $fileName,
            ]);
        }
        
        return response()->json(['status' => 'Files uploaded successfully']);
    }

    public function destroy(ProjectFile $file)
    {
        $file->delete();
        return redirect()->back();
    }

    public function preview($filename)
    {
    $filePath = storage_path('app/uploads/projects/' . $filename);

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

    public function syncAll(Request $request, Project $project)
    {
    Log::debug('====================================');
    $request->validate([
        'mergeFileName' => 'required|string|max:255',
    ]);

    $mergeFileName = $request->input('mergeFileName');
    $files = $project->files()->where('enabled', true)->get();
    $flaskApiUrl = 'http://127.0.0.1:5000/upload'; // Replace with your Flask API URL
    Log::debug('[][][][][][[][][][][][][][][][][][][');
    // Check if there are files to be sent
    if ($files->isEmpty()) {
        return response()->json(['message' => 'No files to synchronize.'], 400);
    }
    Log::debug('==================^^^^^^^^^^^==================');
    $request = Http::asMultipart();

    // Prepare the files to be sent
    foreach ($files as $file) {
        $filePath = storage_path("app/uploads/projects/" . $file->file);

        // Check if the file exists before adding to the multipart data
        if (!file_exists($filePath)) {
            return response()->json(['message' => "File not found: {$filePath}"], 400);
        }

        $request = $request->attach(
            'files', fopen($filePath, 'r'), $file->filename
        );
    }
    Log::debug('=================$$$===================');
    // Make the POST request to the Flask API
    $response = $request->post($flaskApiUrl);

    // Handle the response
    if ($response->successful()) {
        $contentDisposition = $response->header('Content-Disposition');
        $fileName = $mergeFileName . '.xlsx';
        Log::info("Extracted Filename: " . $fileName);

        // Define the final storage path
        $finalFilePath = ResultFile::$FOLDER_PATH . '/' . $fileName;

        // Store the file directly in the final storage path
        Storage::put($finalFilePath, $response->body());

        // Create a new ResultFile record
        $resultFile = ResultFile::create([
            'project_id' => $project->id,
            'file' => $fileName,
        ]);

        return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'results'])
            ->with('success', 'All files synchronized successfully.');
    } else {
        // Handle error
        return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
            ->with('error', 'Failed to synchronize files.');
    }
    }

    public function toggleEnabled(Request $request)
    {
        $file = ProjectFile::find($request->file_id);
        if ($file) {
            $file->enabled = !$file->enabled;
            $file->save();
            return redirect()->back()->with('success', 'File status updated successfully.');
        }
        return redirect()->back()->with('error', 'Failed to update file status.');
    }
}
