<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_team = Auth::user()->currentTeam;
        $projects = Project::where('team_id', $user_team->id)->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        // Create the project with the team_id
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'team_id' => $currentTeam->id,
        ]);
        return redirect()->route('projects.show', $project->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get the current team of the user
        $currentTeam = $user->currentTeam;

        // Ensure the user has a current team and it matches the project's team_id
        if (!$currentTeam || $currentTeam->id !== $project->team_id) {
            abort(403, 'You do not have access to this project.');
        }
    
        // Fetch all project files
        $files = $project->files; // Adjust based on your actual relationship
    
        // Get columns for each file
        $fileDetails = [];
        foreach ($files as $file) {
            $path = storage_path('app/uploads/projects/' . $file->file);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $header = [];

            if ($extension === 'csv') {
                $csv = Reader::createFromPath($path, 'r');
                $csv->setHeaderOffset(0); // Set to 0 for CSV with headers
                $header = $csv->getHeader();
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $spreadsheet = IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $header = $sheet->rangeToArray('1:1')[0]; // Read the first row as header
            }

            // Filter out empty columns
            $fileDetails[] = [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'columns' => array_filter($header, function ($column) {
                    return !empty(trim($column));
                }),
            ];
        }
        // Render the project view
        return view('projects.show', compact('project', 'fileDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);


        $project->update($request->all());
        return redirect()->route('projects.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index');
    }
}
