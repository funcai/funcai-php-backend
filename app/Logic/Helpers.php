<?php

namespace App\Logic;

class Helpers {
    public static function base64_to_file($base64_string) {
        $filename = tempnam(sys_get_temp_dir(), "funcai-image");
        $data = explode( ',', $base64_string );

        file_put_contents($filename, base64_decode($data[1]));

        return $filename;
    }
}
