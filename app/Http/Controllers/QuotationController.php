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

        // 過濾掉空白項目
        $data['items'] = array_filter($data['items'], function ($item) {
            return !empty($item['name']) && !empty($item['amount']);
        });

        // 產生 PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('data'));

        return $pdf->download('quotation.pdf');
    }

}
