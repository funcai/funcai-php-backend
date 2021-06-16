<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInference;
use App\Logic\Helpers;
use Carbon\Carbon;
use FuncAI\Config;
use FuncAI\Models\Imagenet21k;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ImageClassificationController extends Controller
{
    public function classify(Request $request)
    {

        $inputImage = Helpers::base64_to_file($request->get('image'));

        $cacheKey = 'image-classification-' . md5_file($inputImage);
        $cachedResult = Cache::get($cacheKey);
        if($cachedResult) {
            return response()->json([
                'status' => 'done',
                'result' => $cachedResult,
            ]);
        }
        Bus::chain([
            new ProcessInference($inputImage, Imagenet21k::class, $cacheKey),
            function() use ($inputImage) {
                unlink($inputImage);
            }
        ])->dispatch();

        return response()->json([
            'status' => 'processing',
            'key' => $cacheKey,
        ]);
    }

    public function status($cacheKey)
    {
        $result = Cache::get($cacheKey);
        if($result) {
            return response()->json([
                'status' => 'done',
                'result' => $result,
            ]);
        }
        return response()->json([
            'status' => 'processing',
            'key' => $cacheKey,
        ]);
    }
}
