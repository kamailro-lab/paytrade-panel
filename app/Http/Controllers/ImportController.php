<?php

namespace App\Http\Controllers;

use App\Services\SheetsImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function form(): View
    {
        return view('import.form');
    }

    public function store(Request $request, SheetsImporter $importer): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $stats = $importer->importCsv($request->file('csv')->getRealPath());

        return back()
            ->with('import_stats', $stats)
            ->with('success', sprintf(
                'Import: dodano %d, zaktualizowano %d, pominięto %d.',
                $stats['created'], $stats['updated'], $stats['skipped']
            ));
    }
}
