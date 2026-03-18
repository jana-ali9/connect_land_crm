<?php

namespace App\Http\Controllers;

use App\Models\ContractImage;
use Illuminate\Http\Request;

class ContractImageController extends Controller
{
    public function destroy( ContractImage $contractImage)
    {
        if (file_exists($contractImage->image)) {
            unlink($contractImage->image);
        }
        $contractImage->delete();
        return redirect()->back();
    }
}
