<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    public function create()
    {
        return view('quotation.form');
    }

//    public function store(Request $request)
//    {
//        $data = $request->all();
//
//        // 過濾掉空白項目
//        $data['items'] = array_filter($data['items'], function ($item) {
//            return !empty($item['name']) && !empty($item['amount']);
//        });
//
//        // 產生 PDF
//        $pdf = Pdf::loadView('quotation.pdf', compact('data'));
//
//        return $pdf->download('quotation.pdf');
//    }

    public function store(Request $request)
    {
        $data = $request->all();

        // 過濾掉空白項目
        $data['items'] = array_filter($data['items'], function ($item) {
            return !empty($item['name']) && !empty($item['amount']);
        });
        $file = 'movepro' . date('YmdHis') . 'pdf';
        // 產生 PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('data'));

        // 直接上傳到 Cloudflare R2
        $pdfPath = date('Ymd') . '/' . $file;
        Storage::disk('movepro')->put($pdfPath, $pdf->output());

        // 取得 R2 上的 URL
//        $r2Url = Storage::disk('r2')->url($pdfPath);

        // 返回 PDF 下載，直接從 R2 讀取
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $file);
    }

}
