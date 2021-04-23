<?php

namespace App\Jobs;

use Carbon\Carbon;
use FuncAI\Config;
use FuncAI\Models\Stylization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessInference implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    private $input;
    private $modelClass;
    private $cacheKey;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input, $modelClass, $cacheKey)
    {
        $this->input = $input;
        $this->modelClass = $modelClass;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Setup your folder paths
        Config::setModelBasePath('/var/www/html/models');
        Config::setLibPath('/var/www/html/tensorflow/');

        // We're using a dynamic class name here
        // You can also just do:
        // $model = new \FuncAI\Models\Imagenet21k();
        $className = $this->modelClass;
        $model = new $className();

        // Get the prediction from the model
        $result = $model->predict($this->input);

        // TODO: Clean this up, so that we don't need exceptions for different models in our job
        if($this->modelClass === Stylization::class) {
            $data = file_get_contents('out.jpg');
            $result = 'data:image/jpg;base64,' . base64_encode($data);
        }

        // Cache the result (optional)
        Cache::put($this->cacheKey, $result, Carbon::now()->addDays(30));
    }
}
