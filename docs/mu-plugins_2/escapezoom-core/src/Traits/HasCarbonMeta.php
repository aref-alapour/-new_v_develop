<?php

namespace EscapeZoom\Core\Traits;

/**
 * Trait HasCarbonMeta
 * 
 * این Trait امکان خواندن متادیتای Carbon Fields را به مدل‌های Eloquent اضافه می‌کند
 * 
 * استفاده:
 * ```php
 * use EscapeZoom\Core\Traits\HasCarbonMeta;
 * 
 * class Post extends Model {
 *     use HasCarbonMeta;
 * }
 * 
 * // دریافت متادیتا
 * $post->getMeta('my_custom_field');
 * $post->getMeta('my_custom_field', 'default_value');
 * 
 * // دریافت تمام متادیتا
 * $post->getAllMeta();
 * ```
 */
trait HasCarbonMeta
{
    /**
     * کش متادیتا برای جلوگیری از query های تکراری
     *
     * @var array|null
     */
    protected ?array $metaCache = null;

    /**
     * دریافت یک مقدار متادیتا
     *
     * @param string $key
     * @param mixed $default
     * @param bool $single
     * @return mixed
     */
    public function getMeta(string $key, $default = null, bool $single = true)
    {
        // برای پست‌ها و صفحات
        if (property_exists($this, 'table') && in_array($this->table, ['wp_posts', 'posts'])) {
            $metaValue = get_post_meta($this->getKey(), $key, $single);
            
            // اگر مقدار خالی بود، default را برگردان
            if ($metaValue === '' || $metaValue === false) {
                return $default;
            }
            
            // تلاش برای unserialize اگر serialized باشد
            if (is_string($metaValue) && $this->isSerialized($metaValue)) {
                return maybe_unserialize($metaValue);
            }
            
            return $metaValue ?: $default;
        }
        
        // برای کاربران
        if (property_exists($this, 'table') && in_array($this->table, ['wp_users', 'users'])) {
            $metaValue = get_user_meta($this->getKey(), $key, $single);
            
            if ($metaValue === '' || $metaValue === false) {
                return $default;
            }
            
            if (is_string($metaValue) && $this->isSerialized($metaValue)) {
                return maybe_unserialize($metaValue);
            }
            
            return $metaValue ?: $default;
        }
        
        // برای terms
        if (property_exists($this, 'table') && in_array($this->table, ['wp_terms', 'terms'])) {
            $metaValue = get_term_meta($this->getKey(), $key, $single);
            
            if ($metaValue === '' || $metaValue === false) {
                return $default;
            }
            
            if (is_string($metaValue) && $this->isSerialized($metaValue)) {
                return maybe_unserialize($metaValue);
            }
            
            return $metaValue ?: $default;
        }
        
        return $default;
    }

    /**
     * دریافت تمام متادیتای یک پست/کاربر/term
     *
     * @return array
     */
    public function getAllMeta(): array
    {
        if ($this->metaCache !== null) {
            return $this->metaCache;
        }

        // برای پست‌ها و صفحات
        if (property_exists($this, 'table') && in_array($this->table, ['wp_posts', 'posts'])) {
            $meta = get_post_meta($this->getKey());
            $this->metaCache = $this->formatMetaArray($meta);
            return $this->metaCache;
        }
        
        // برای کاربران
        if (property_exists($this, 'table') && in_array($this->table, ['wp_users', 'users'])) {
            $meta = get_user_meta($this->getKey());
            $this->metaCache = $this->formatMetaArray($meta);
            return $this->metaCache;
        }
        
        // برای terms
        if (property_exists($this, 'table') && in_array($this->table, ['wp_terms', 'terms'])) {
            $meta = get_term_meta($this->getKey());
            $this->metaCache = $this->formatMetaArray($meta);
            return $this->metaCache;
        }

        return [];
    }

    /**
     * تنظیم یک مقدار متادیتا
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setMeta(string $key, $value): bool
    {
        // پاک کردن کش
        $this->metaCache = null;
        
        // برای پست‌ها و صفحات
        if (property_exists($this, 'table') && in_array($this->table, ['wp_posts', 'posts'])) {
            return update_post_meta($this->getKey(), $key, $value) !== false;
        }
        
        // برای کاربران
        if (property_exists($this, 'table') && in_array($this->table, ['wp_users', 'users'])) {
            return update_user_meta($this->getKey(), $key, $value) !== false;
        }
        
        // برای terms
        if (property_exists($this, 'table') && in_array($this->table, ['wp_terms', 'terms'])) {
            return update_term_meta($this->getKey(), $key, $value) !== false;
        }
        
        return false;
    }

    /**
     * حذف یک متادیتا
     *
     * @param string $key
     * @return bool
     */
    public function deleteMeta(string $key): bool
    {
        // پاک کردن کش
        $this->metaCache = null;
        
        // برای پست‌ها و صفحات
        if (property_exists($this, 'table') && in_array($this->table, ['wp_posts', 'posts'])) {
            return delete_post_meta($this->getKey(), $key);
        }
        
        // برای کاربران
        if (property_exists($this, 'table') && in_array($this->table, ['wp_users', 'users'])) {
            return delete_user_meta($this->getKey(), $key);
        }
        
        // برای terms
        if (property_exists($this, 'table') && in_array($this->table, ['wp_terms', 'terms'])) {
            return delete_term_meta($this->getKey(), $key);
        }
        
        return false;
    }

    /**
     * دریافت Carbon Field با استفاده از تابع carbon_get_*
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getCarbonField(string $key, $default = null)
    {
        // برای پست‌ها و صفحات
        if (property_exists($this, 'table') && in_array($this->table, ['wp_posts', 'posts'])) {
            if (function_exists('carbon_get_post_meta')) {
                $value = carbon_get_post_meta($this->getKey(), $key);
                return $value ?: $default;
            }
        }
        
        // برای کاربران
        if (property_exists($this, 'table') && in_array($this->table, ['wp_users', 'users'])) {
            if (function_exists('carbon_get_user_meta')) {
                $value = carbon_get_user_meta($this->getKey(), $key);
                return $value ?: $default;
            }
        }
        
        // برای terms
        if (property_exists($this, 'table') && in_array($this->table, ['wp_terms', 'terms'])) {
            if (function_exists('carbon_get_term_meta')) {
                $value = carbon_get_term_meta($this->getKey(), $key);
                return $value ?: $default;
            }
        }
        
        // fallback به getMeta
        return $this->getMeta($key, $default);
    }

    /**
     * فرمت کردن آرایه متادیتا (از [[value]] به value)
     *
     * @param array $meta
     * @return array
     */
    protected function formatMetaArray(array $meta): array
    {
        $formatted = [];
        
        foreach ($meta as $key => $values) {
            if (is_array($values) && count($values) === 1) {
                $value = $values[0];
                
                if (is_string($value) && $this->isSerialized($value)) {
                    $formatted[$key] = maybe_unserialize($value);
                } else {
                    $formatted[$key] = $value;
                }
            } else {
                $formatted[$key] = $values;
            }
        }
        
        return $formatted;
    }

    /**
     * چک کردن serialized بودن یک رشته
     *
     * @param mixed $data
     * @return bool
     */
    protected function isSerialized($data): bool
    {
        if (!is_string($data)) {
            return false;
        }
        
        $data = trim($data);
        
        if ($data === 'N;') {
            return true;
        }
        
        if (strlen($data) < 4) {
            return false;
        }
        
        if ($data[1] !== ':') {
            return false;
        }
        
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        
        $token = $data[0];
        switch ($token) {
            case 's':
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
                // no break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = substr($data, -1);
                return $end === ';';
        }
        
        return false;
    }

    /**
     * پاک کردن کش متادیتا
     *
     * @return void
     */
    public function clearMetaCache(): void
    {
        $this->metaCache = null;
    }
}
