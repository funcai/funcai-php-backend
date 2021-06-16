<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInference;
use App\Logic\Helpers;
use Carbon\Carbon;
use FuncAI\Config;
use FuncAI\Models\Imagenet21k;
use FuncAI\Models\Stylization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ImageStylizationController extends Controller
{
    public function stylize(Request $request)
    {
        $inputImage = Helpers::base64_to_file($request->get('image'));
        $inputStyle = Helpers::base64_to_file($request->get('style'));

        $cacheKey = 'image-stylization-' . md5_file($inputImage) . '-' . md5_file($inputStyle);
        $cachedResult = Cache::get($cacheKey);
        if($cachedResult) {
            return response()->json([
                'status' => 'done',
                'result' => $cachedResult,
            ]);
        }
        Bus::chain([
            new ProcessInference([
                $inputImage,
                $inputStyle,
            ], Stylization::class, $cacheKey),
            function() use ($inputImage, $inputStyle) {
                unlink($inputImage);
                unlink($inputStyle);
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
