<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IsoStandardController extends Controller
{
    public function index()
    {
        // Get root standards separated by type (supporting both clause and clausa)
        $clauses = \App\Models\IsoStandard::with('children.children')
            ->whereNull('parent_id')
            ->whereIn('type', ['clause', 'clausa'])
            ->orderByRaw('LENGTH(code) ASC, code ASC')
            ->get();
            
        $controls = \App\Models\IsoStandard::with('children.children')
            ->whereNull('parent_id')
            ->where('type', 'control')
            ->orderByRaw('LENGTH(code) ASC, code ASC')
            ->get();

        return view('admin.standards.index', compact('clauses', 'controls'));
    }

    public function create()
    {
        $parents = \App\Models\IsoStandard::orderByRaw('LENGTH(code) ASC, code ASC')->get();
        return view('admin.standards.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:iso_standards,id',
            'type' => 'required|in:clause,clausa,control',
            'level' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:iso_standards,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'nullable|array',
            'questions.*' => 'required|string',
            'implementation_guidance' => 'nullable|string',
        ]);

        \App\Models\IsoStandard::create($validated);

        return redirect()->route('admin.standards.index')->with('success', 'ISO Standard created successfully.');
    }

    public function edit(\App\Models\IsoStandard $standard)
    {
        $parents = \App\Models\IsoStandard::where('id', '!=', $standard->id)->orderByRaw('LENGTH(code) ASC, code ASC')->get();
        return view('admin.standards.edit', compact('standard', 'parents'));
    }

    public function update(Request $request, \App\Models\IsoStandard $standard)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:iso_standards,id',
            'type' => 'required|in:clause,clausa,control',
            'level' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:iso_standards,code,' . $standard->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'nullable|array',
            'questions.*' => 'required|string',
            'implementation_guidance' => 'nullable|string',
        ]);

        // Prevent setting itself as parent
        if ($validated['parent_id'] == $standard->id) {
            return back()->withErrors(['parent_id' => 'A standard cannot be its own parent.'])->withInput();
        }

        $standard->update($validated);

        return redirect()->route('admin.standards.index')->with('success', 'ISO Standard updated successfully.');
    }

    public function destroy(\App\Models\IsoStandard $standard)
    {
        // Check if there are any child standards
        if ($standard->children()->count() > 0) {
            return back()->with('error', 'Cannot delete this standard because it has child clauses/controls. Please delete them first.');
        }

        // Check if this standard is used in assessment results
        if ($standard->results()->count() > 0) {
            return back()->with('error', 'Cannot delete this standard because it has been used in user assessment sessions. Deleting it would break historical data.');
        }

        $standard->delete();

        return redirect()->route('admin.standards.index')->with('success', 'ISO Standard deleted successfully.');
    }

    public function export()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=iso27001_standards_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $standards = \App\Models\IsoStandard::with('parent')->orderByRaw('LENGTH(code) ASC, code ASC')->get();

        $callback = function() use($standards) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'parent_code',
                'type',
                'level',
                'code',
                'title',
                'description',
                'questions',
                'implementation_guidance'
            ]);

            foreach ($standards as $row) {
                $parentCode = $row->parent ? $row->parent->code : '';
                fputcsv($file, [
                    $parentCode,
                    $row->type,
                    $row->level,
                    $row->code,
                    $row->title,
                    $row->description,
                    is_array($row->questions) ? json_encode($row->questions) : $row->questions,
                    $row->implementation_guidance
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        
        $csvData = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Get headers
            $headers = fgetcsv($handle, 1000, ",");
            
            // Read all rows
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= count($headers)) {
                    $csvData[] = array_combine($headers, array_slice($data, 0, count($headers)));
                }
            }
            fclose($handle);
        }

        // Run transaction
        \DB::transaction(function () use ($csvData) {
            // Step 1: Create/Update all standards without parent_id (or keep it null for now) to prevent key issues
            foreach ($csvData as $row) {
                // Decode questions
                $questions = null;
                if (!empty($row['questions'])) {
                    $decoded = json_decode($row['questions'], true);
                    $questions = is_array($decoded) ? $decoded : [$row['questions']];
                }

                \App\Models\IsoStandard::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'type' => $row['type'],
                        'level' => $row['level'],
                        'title' => $row['title'],
                        'description' => !empty($row['description']) ? $row['description'] : null,
                        'questions' => $questions,
                        'implementation_guidance' => !empty($row['implementation_guidance']) ? $row['implementation_guidance'] : null,
                    ]
                );
            }

            // Step 2: Update parent_id based on parent_code from CSV
            foreach ($csvData as $row) {
                if (!empty($row['parent_code'])) {
                    $parent = \App\Models\IsoStandard::where('code', $row['parent_code'])->first();
                    if ($parent) {
                        $child = \App\Models\IsoStandard::where('code', $row['code'])->first();
                        if ($child) {
                            $child->parent_id = $parent->id;
                            $child->save();
                        }
                    }
                } else {
                    $child = \App\Models\IsoStandard::where('code', $row['code'])->first();
                    if ($child) {
                        $child->parent_id = null;
                        $child->save();
                    }
                }
            }
        });

        return redirect()->route('admin.standards.index')->with('success', 'ISO Standards imported and updated successfully.');
    }
}
