<?php

namespace Modules\Starter\Entities;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Starter\Traits\Paginatable;

class BaseModel extends Model
{
    use Paginatable;

    protected array $methods = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->bindAppendMethods();
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function bindAppendMethods()
    {
        if (count($this->appends)) {
            foreach ($this->appends as $append) {
                $method_name = 'get' . Str::studly($append) . 'Attribute';
                if (!method_exists($this, $method_name)) {
                    $this->methods[$method_name] = function ($value) use ($append) {
                        //为 _str 结尾的数值类型 append 添加 Getter
                        if (Str::endsWith($append, '_str') && is_numeric($original = $this->__get(substr($append, 0, -4)))) {
                            //1. 格式化数字
                            return sprintf("%.2f", $original);
                        } elseif (Str::endsWith($append, '_date')) {
                            //2. 只获取日期
                            $original = $this->__get(substr($append, 0, -5));
                            if ($original instanceof Carbon) {
                                return $original->toDateString();
                            } else {
                                return '';
                            }
                        } elseif (Str::endsWith($append, '_datetime')) {
                            //2. 只获取日期
                            $original = $this->__get(substr($append, 0, -9));
                            if ($original instanceof Carbon) {
                                return $original->format('Y-m-d H:i');
                            } else {
                                return '';
                            }
                        } elseif (Str::endsWith($append, '_human')) {
                            //3. 时间人性化
                            $original = $this->__get(substr($append, 0, -6));
                            if ($original instanceof Carbon) {
                                return $original->diffForHumans();
                            } else {
                                return '';
                            }
                        }
                    };
                }
            }
        }
    }

    public function __call($method, $parameters)
    {

        if (array_key_exists($method, $this->methods)) {
            return call_user_func_array($this->methods[$method], $parameters);
        }

        #i: No relation found, return the call to parent (Eloquent) to handle it.
        return parent::__call($method, $parameters);
    }


    protected $appends = [];

    protected $hidden = ['deleted_at'];

    protected $guarded = [];

    public function restoreStorage($value, $is_array = false, Filesystem $storage = null): array
    {

        if (empty($value)) {
            return $is_array ? [] : ['url' => '', 'path' => ''];
        }

        if ($is_array) {
            $values = json_decode($value, true);

            if (!$values || count($values) === 0) {
                return [];
            } else {
                return array_map(function ($value) use ($storage) {
                    return array_merge($value, ['url' => $storage ? $storage->url($value['path']) : Storage::url($value['path'])]);
                }, $values);
            }
        } else {
            $value = json_decode($value, true);
            return array_merge($value, ['url' => $storage ? $storage->url($value['path']) : Storage::url($value['path'])]);
        }

    }
}
