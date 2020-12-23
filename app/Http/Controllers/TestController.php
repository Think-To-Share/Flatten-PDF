<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;

class TestController extends Controller
{
    public function test() {
        $pdf = new Fpdi('', '');
        $pdf->setSourceFile('');
        $pdf->setSignature();
        $pdf->useTemplate();
    }
}
