<?php namespace App\Http\Controllers\Api\V1;

use PicoPrime\BarcodeGen\BarcodeGenerator;
use App\Http\Controllers\Controller;

class BarcodeController extends Controller
{

    public function __construct(BarcodeGenerator $barcode)
    {
        $this->barcode = $barcode;
    }

    public function scanOrderNum()
    {
        return view('barcode.scan');
    }

    public function barcodeAsPng(
        $text = '',
        $size = 50,
        $scale = 1,
        $orientation = 'horizontal',
        $codeType = 'code128'
    ) {
        return $this->barcode
            ->generate(compact('text', 'size', 'scale', 'orientation', 'codeType'))
            ->response('png');
    }

    public function barcodeAsDataUrl(
        $text = '',
        $size = 50,
        $scale = 1,
        $orientation = 'horizontal',
        $codeType = 'code128'
    ) {
        return $this->barcode
            ->generate(compact('text', 'size', 'scale', 'orientation', 'codeType'))
            ->encode('data-url');
    }
}
