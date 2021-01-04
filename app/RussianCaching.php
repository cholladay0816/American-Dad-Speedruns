<?php
namespace App;

use Illuminate\Support\Facades\Cache;

class RussianCaching
{
    protected static $keys = [];

    public static function setUp($model)
    {
        static::$keys[] = $key = $model->getCacheKey();

        ob_start();

        return Cache::has($key);
    }
    public static function tearDown()
    {
        $key = array_pop(static::$keys);

        $html = ob_get_clean();

        return Cache::remember($key, now()->addDays(31), function() use ($html) {
            return $html;
        });
    }



}
