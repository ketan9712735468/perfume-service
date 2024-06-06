<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResultFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ResultFileController extends Controller
{
    public function destroy(ResultFile $resultFile)
    {
        $resultFile->delete();
        return redirect()->back();
    }

    public function preview($filename)
    {
    $filePath = storage_path('app/uploads/results/' . $filename);

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
}
