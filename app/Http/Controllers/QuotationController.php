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

    public function create2()
    {
        return view('quotation.form2');
    }

    public function store2(Request $request)
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

    public function store4(Request $request)
    {
        $data = $request->all();

        // 過濾掉空白項目
        $data['items'] = array_filter($data['items'], function ($item) {
            return !empty($item['name']) && !empty($item['amount']);
        });
//        $file = 'quotation.pdf';
        $file = '' . date('YmdHi') . '.pdf';
//        $file = '樂遷搬家報價單_' . date('YmdHi') . '.pdf';
        // 產生 PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('data'));

        // 直接上傳到 Cloudflare R2
        $pdfPath = date('Ymd') . '/' . $file;
        Storage::disk('movepro')->put($pdfPath, $pdf->output());

        // 取得 R2 上的 URL
//        $r2Url = Storage::disk('movepro')->url($pdfPath);

        // 返回 PDF 下載，直接從 R2 讀取
//        return response()->streamDownload(function () use ($pdf) {
//            echo $pdf->output();
//        }, $file);
//        return response()->streamDownload(function () use ($pdf) {
//            echo $pdf->output();
//        }, $file, [
//            'Content-Type' => 'application/pdf',
//            'Content-Disposition' => 'attachment; filename="' . rawurlencode($file) . '"',
//        ]);
        return $pdf->download($file);
//        return response()->make($pdf->output(), 200, [
//            'Content-Type' => 'application/pdf',
//            'Content-Disposition' => 'attachment; filename="' . rawurlencode($file) . '"',
//        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // 過濾掉空白項目
        $data['items'] = array_filter($data['items'], function ($item) {
            return !empty($item['name']) && !empty($item['amount']);
        });

        $file = date('YmdHi') . '.pdf';

        // 產生 PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('data'));
        $pdfContent = $pdf->output();

        // 直接上傳到 Cloudflare R2
        $pdfPath = date('Ymd') . '/' . $file;
        Storage::disk('movepro')->put($pdfPath, $pdfContent);

        // 返回 PDF 下載（使用已儲存的內容）
        return response()->make($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($file) . '"',
        ]);
    }

}
