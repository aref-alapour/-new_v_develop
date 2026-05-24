<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Model;
use Exception;

/**
 * مدل آپشن وردپرس (wp_options).
 */
class Option extends Model
{
    protected $connection = 'wordpress';
    protected $table = 'options';
    protected $primaryKey = 'option_id';
    public $timestamps = false;

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];

    protected $appends = ['value'];

    public function getValueAttribute()
    {
        try {
            $value = @unserialize($this->option_value);
            return ($value === false && $this->option_value !== false)
                ? $this->option_value
                : $value;
        } catch (Exception $e) {
            return $this->option_value;
        }
    }

    /** پیدا کردن با نام آپشن */
    public static function getByName(string $name): ?self
    {
        return static::where('option_name', $name)->first();
    }

    /** مقدار یک آپشن با نام */
    public static function value(string $name, $default = null)
    {
        $option = static::getByName($name);
        return $option ? $option->value : $default;
    }
}
