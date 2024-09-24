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
use Aws\Lambda\LambdaClient;


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
        $projectfile = ProjectFile::find($filename);
        $path = storage_path('app/uploads/projects/' . $projectfile->file);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response()->download($path, $projectfile->original_name, ['Content-Type' => $type]);
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
        // $flaskApiUrl = 'http://127.0.0.1:5000/upload'; // Local Flask API URL
        $flaskApiUrl = "http://16.171.137.198:5000/upload"; // Live Flask API URL
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
        
        // Prepare the data for the Lambda invocation
        $data = [
            'commonColumns' => $commonColumns,
            'fileColumns' => $fileColumns,
        ];
    
        Log::info('Data prepared for Lambda invocation', ['data' => $data]);
    
        // Retrieve the files to attach
        $files = $project->files()->where('enabled', true)->get();
        $attachments = [];
        
        // Prepare file paths and validate existence
        foreach ($files as $file) {
            $filePath = storage_path("app/uploads/projects/" . $file->file);
            Log::debug('Processing file', ['filePath' => $filePath]);
    
            // Check if the file exists before adding to the attachments
            if (!file_exists($filePath)) {
                Log::error('File not found', ['filePath' => $filePath]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', "File not found: {$filePath}");
            }
    
            $attachments[] = [
                'filename' => $file->original_name,
                'content' => base64_encode(file_get_contents($filePath)),
            ];
        }
    
        // Add files to data payload
        $data['files'] = $attachments;
    
        // Initialize the AWS Lambda client
        $lambdaClient = new LambdaClient([
            'version' => 'latest',
            'region'  => config('services.aws.region'),
            'credentials' => [
                'key'    => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
    
        try {
            // Invoke the Lambda function
            $result = $lambdaClient->invoke([
                'FunctionName' => 'manualUpload', // Replace with your Lambda function name
                'InvocationType' => 'RequestResponse',      // 'Event' for async
                'Payload' => json_encode($data),
            ]);
    
            // Decode the Lambda response
            $responsePayload = json_decode((string) $result->get('Payload'), true);
            Log::info('Lambda function response');

            // Decode the 'body' field
            $body = json_decode($responsePayload['body'], true); // Parse the body as JSON

            if (isset($body['fileContent'])) {
                $fileName = 'projects_' . time() . '_' . Str::random(10) . '.xlsx';
                Log::info("Extracted Filename", ['fileName' => $fileName]);

                // Decode the file content
                $fileContent = base64_decode($body['fileContent']);

                // Define the final storage path
                $finalFilePath = ResultFile::$FOLDER_PATH . '/' . $fileName;

                // Store the file directly in the final storage path
                Storage::put($finalFilePath, $fileContent);

                // Create a new ResultFile record
                ResultFile::create([
                    'project_id' => $project->id,
                    'file' => $fileName,
                    'original_name' => $mergeFileName . '.xlsx',
                ]);

                Log::info('File synchronized successfully', ['finalFilePath' => $finalFilePath]);

                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'results'])
                    ->with('success', 'All files synchronized successfully.');
            } else {
                Log::error('File content not found in response', ['response' => $responsePayload]);
                return redirect()->route('projects.show', ['project' => $project->id, 'type' => 'files'])
                    ->with('error', 'Failed to synchronize files.');
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

    public function bulkAction(Request $request, $type)
    {
        $fileIds = explode(',', $request->input('file_ids'));

        if (!$fileIds) {
            return back()->with('error', 'No files selected.');
        }

        switch ($type) {
            case 'enable':
                ProjectFile::whereIn('id', $fileIds)->update(['enabled' => true]);
                return back()->with('success', 'Selected files have been disabled.');
            
            case 'disable':
                ProjectFile::whereIn('id', $fileIds)->update(['enabled' => false]);
                return back()->with('success', 'Selected files have been disabled.');
            
            case 'delete':
                ProjectFile::whereIn('id', $fileIds)->delete();
                return back()->with('success', 'Selected files have been deleted.');
            
            default:
                return back()->with('error', 'Invalid action.');
        }
    }
}
