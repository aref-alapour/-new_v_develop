<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Concerns\AdvancedCustomFields;
use Corcel\Concerns\Aliases;
use Corcel\Concerns\MetaFields;
use Corcel\Concerns\OrderScopes;
use Corcel\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * مدل کاربر وردپرس (wp_users) با روابط.
 */
class User extends Model implements Authenticatable, CanResetPassword
{
    use AdvancedCustomFields;
    use Aliases;
    use MetaFields;
    use OrderScopes;

    const CREATED_AT = 'user_registered';
    const UPDATED_AT = null;

    protected $connection = 'wordpress';
    protected $table = 'users';
    protected $primaryKey = 'ID';
    protected $hidden = ['user_pass'];
    protected $dates = ['user_registered'];
    protected $with = ['meta'];

    protected static $aliases = [
        'login' => 'user_login',
        'email' => 'user_email',
        'slug' => 'user_nicename',
        'url' => 'user_url',
        'nickname' => ['meta' => 'nickname'],
        'first_name' => ['meta' => 'first_name'],
        'last_name' => ['meta' => 'last_name'],
        'description' => ['meta' => 'description'],
        'created_at' => 'user_registered',
    ];

    protected $appends = [
        'login',
        'email',
        'slug',
        'url',
        'nickname',
        'first_name',
        'last_name',
        'avatar',
        'created_at',
    ];

    public function setUpdatedAtAttribute($value)
    {
    }

    /** متاهای کاربر */
    public function meta(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'user_id');
    }

    /** پست‌های کاربر */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_author');
    }

    /** کامنت‌های کاربر */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getAuthPassword()
    {
        return $this->user_pass ?? null;
    }

    public function getAuthPasswordName()
    {
        return 'user_pass';
    }

    public function getRememberToken()
    {
        return $this->meta->get('remember_token');
    }

    public function setRememberToken($value)
    {
        $this->meta->set('remember_token', $value);
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    public function sendPasswordResetNotification($token)
    {
    }
}
