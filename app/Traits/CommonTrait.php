<?php
namespace App\Traits;
use Illuminate\Support\Str;
trait CommonTrait
{
    public static function create(array $attributes=[]){
        $attributes['uuid'] = Str::orderedUuid()->toString();
        if (method_exists(self::class, 'setSequence')) {
            $attributes['sequence'] = self::setSequence($attributes);
        }
        $model = static::query()->create($attributes);
        return $model;
    }
    public static function getWithUUID($uuid)
    {
        $data = self::where('uuid', '=', $uuid)->first();
        return $data;
    }
}