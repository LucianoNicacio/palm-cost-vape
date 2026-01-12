<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportController extends Controller
{
    public function showForm()
    {
        return Inertia::render('Admin/Products/Import', [
            'acceptedFormats' => ['.xlsx', '.xls', '.csv'],
            'maxFileSize' => '10MB',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ProductsImport();

        Excel::import($import, $request->file('file'));

        return back()->with(
            'success',
            "Import complete! Created: {$import->created}, Updated: {$import->updated}"
        );
    }

    public function downloadTemplate()
    {
        $csv = "id,name,sku,price,tax,status,track_inv,on_hand,category\n";
        $csv .= "4358027,Raz 25K,01007,26.99,true,active,true,49,01-Disposables-Nic\n";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="template.csv"');
    }
}