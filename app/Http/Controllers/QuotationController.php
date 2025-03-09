<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function create()
    {
        return view('quotation.form');
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // 產生 PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('data'));

        return $pdf->download('quotation.pdf');
    }
}
