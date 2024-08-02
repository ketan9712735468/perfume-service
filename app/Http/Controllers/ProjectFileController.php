<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Auth;
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
        // Get the authenticated user
        $user = Auth::user();
        
        // Get the current team of the user
        $team_id = $user->currentTeam->id;

        // Ensure the user has a current team and it matches the project's team_id
        if ($team_id != $project->team_id) {
            abort(403, 'You do not have access to this project.');
        }
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
            $originalFileName = $file->getClientOriginalName();
            $fileName = 'projects_' . time() . '_' . Str::random(10) . '.' . $extension;
            $file->storeAs(ProjectFile::$FOLDER_PATH, $fileName);

            $project->files()->create([
                'file' => $fileName,
                'original_name' => $originalFileName,
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
        Log::debug('Starting syncAll function');
        
        // Validate the request
        $request->validate([
            'mergeFileName' => 'required|string|max:255',
        ]);

        $mergeFileName = $request->input('mergeFileName');
        $files = $project->files()->where('enabled', true)->get();
        $inventories = $project->inventories()->get();
        $flaskApiUrl = 'http://127.0.0.1:5000/upload'; // Local Flask API URL
        // $flaskApiUrl = "https://perfume-api-9131.onrender.com/upload";
        Log::debug('Checked files and mergeFileName', ['mergeFileName' => $mergeFileName, 'fileCount' => $files->count(), 'inventoryCount' => $inventories->count()]);

        // Check if there are files and inventories to be sent
        if ($files->isEmpty() && $inventories->isEmpty()) {
            Log::warning('No files or inventories to synchronize');
            return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                ->with('error', 'No files or inventories to synchronize.');
        }

        $http = Http::asMultipart()->timeout(0);

        // Prepare the inventories to be sent
        foreach ($inventories as $inventory) {
            $filePath = storage_path("app/uploads/project_inventories/" . $inventory->file);
            Log::debug('Processing inventory file', ['filePath' => $filePath]);

            // Check if the file exists before adding to the multipart data
            if (!file_exists($filePath)) {
                Log::error('File not found', ['filePath' => $filePath]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', "File not found: {$filePath}");
            }

            $http = $http->attach(
                'inventory', fopen($filePath, 'r'), $inventory->original_name
            );
        }

        // Prepare the files to be sent
        foreach ($files as $file) {
            $filePath = storage_path("app/uploads/projects/" . $file->file);
            Log::debug('Processing file', ['filePath' => $filePath]);

            // Check if the file exists before adding to the multipart data
            if (!file_exists($filePath)) {
                Log::error('File not found', ['filePath' => $filePath]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', "File not found: {$filePath}");
            }

            $http = $http->attach(
                'files', fopen($filePath, 'r'), $file->original_name
            );
        }

        Log::debug('All files attached. Sending request to Flask API');

        // Make the POST request to the Flask API
        try {
            $response = $http->post($flaskApiUrl);

            // Handle the response
            if ($response->successful()) {
                $fileName = 'projects_' . time() . '_' . Str::random(10) . '.xlsx';
                Log::info("Extracted Filename", ['fileName' => $fileName]);

                // Define the final storage path
                $finalFilePath = ResultFile::$FOLDER_PATH . '/' . $fileName;

                // Store the file directly in the final storage path
                Storage::put($finalFilePath, $response->body());

                // Create a new ResultFile record
                $resultFile = ResultFile::create([
                    'project_id' => $project->id,
                    'file' => $fileName,
                    'original_name' => $mergeFileName . '.xlsx'
                ]);

                Log::info('File synchronized successfully', ['finalFilePath' => $finalFilePath]);

                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'results'])
                    ->with('success', 'All files synchronized successfully.');
            } else {
                Log::error('Failed to synchronize files', ['response' => $response->body()]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', 'Failed to synchronize files.');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred during synchronization', ['exception' => $e->getMessage()]);
            return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                ->with('error', 'An exception occurred during synchronization.');
        }
    }

    public function manualSync(Request $request, Project $project)
    {
        // Retrieve the common columns selected
        $commonColumns = $request->input('commonColumn');

        // Retrieve the file columns selected
        $fileColumns = $request->input('columns');
        
        // Retrieve the merge file name
        $mergeFileName = $request->input('mergeFileName');

        // Prepare the data for the API call
        $data = [
            'commonColumns' => $commonColumns,
            'fileColumns' => $fileColumns,
        ];

        Log::info('Data prepared for API call', ['data' => $data]);

        // Retrieve the files to attach
        $files = $project->files()->where('enabled', true)->get();

        // Initialize HTTP client
        $http = Http::asMultipart()->timeout(0);

        // Attach additional data to the request
        $http->attach('commonColumns', json_encode($data['commonColumns']));
        $http->attach('fileColumns', json_encode($data['fileColumns']));
        
        // Prepare the multipart data
        foreach ($files as $file) {
            $filePath = storage_path("app/uploads/projects/" . $file->file);
            Log::debug('Processing file', ['filePath' => $filePath]);

            // Check if the file exists before adding to the multipart data
            if (!file_exists($filePath)) {
                Log::error('File not found', ['filePath' => $filePath]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', "File not found: {$filePath}");
            }

            // Attach each file to the request
            $http->attach(
                'files', fopen($filePath, 'r'), $file->original_name
            );
        }

        $flaskApiUrl = 'http://127.0.0.1:5000/manual-upload'; // Local Flask API URL

        try {
            // Send the POST request
            $response = $http->post($flaskApiUrl);

            // Handle the response
            if ($response->successful()) {
                $fileName = 'projects_' . time() . '_' . Str::random(10) . '.xlsx';
                Log::info("Extracted Filename", ['fileName' => $fileName]);

                // Define the final storage path
                $finalFilePath = ResultFile::$FOLDER_PATH . '/' . $fileName;

                // Store the file directly in the final storage path
                Storage::put($finalFilePath, $response->body());

                // Create a new ResultFile record
                $resultFile = ResultFile::create([
                    'project_id' => $project->id,
                    'file' => $fileName,
                    'original_name' => $mergeFileName . '.xlsx'
                ]);

                Log::info('File synchronized successfully', ['finalFilePath' => $finalFilePath]);

                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'results'])
                    ->with('success', 'All files synchronized successfully.');
            } else {
                Log::error('Failed to synchronize files', ['response' => $response->body()]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', 'Failed to synchronize files. ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred during synchronization', ['exception' => $e->getMessage()]);
            return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                ->with('error', 'An exception occurred during synchronization.');
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
