<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInference;
use App\Logic\Helpers;
use Carbon\Carbon;
use FuncAI\Models\BitMR50x1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ImageSimilarityController extends Controller
{
    public function similarity(Request $request)
    {
        $inputImage1 = Helpers::base64_to_file($request->get('image1'));
        $inputImage2 = Helpers::base64_to_file($request->get('image2'));

        $cacheKey = 'image-similarity-' . md5_file($inputImage1) . '-' . md5_file($inputImage2);
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult) {
            return response()->json([
                'status' => 'done',
                'result' => $cachedResult,
            ]);
        }
        Bus::chain([
            new ProcessInference($inputImage1, BitMR50x1::class, $cacheKey . '-image1'),
            new ProcessInference($inputImage2, BitMR50x1::class, $cacheKey . '-image2'),
            function () use ($inputImage1, $inputImage2, $cacheKey) {
                $vector1 = Cache::get($cacheKey . '-image1');
                $vector2 = Cache::get($cacheKey . '-image2');
                Cache::put($cacheKey, $this->vectorSimilarity($vector1, $vector2), Carbon::now()->addDays(30));
                unlink($inputImage1);
                unlink($inputImage2);
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
        if ($result) {
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

    private function vectorSimilarity(&$a, &$b)
    {
        $prod = 0.0;
        $v1_norm = 0.0;
        foreach ($a as $i => $xi) {
            $prod += $xi * $b[$i];
            $v1_norm += $xi * $xi;
        }
        $v1_norm = sqrt($v1_norm);

        $v2_norm = 0.0;
        foreach ($b as $i => $xi) {
            $v2_norm += $xi * $xi;
        }
        $v2_norm = sqrt($v2_norm);

        return $prod / ($v1_norm * $v2_norm);
    }
}
