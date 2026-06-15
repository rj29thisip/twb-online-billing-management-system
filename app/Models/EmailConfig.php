<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;

class EmailConfig extends Model
{
    protected $fillable = [
        'mailer', 'host', 'port', 'username', 'password',
        'encryption', 'from_address', 'from_name', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port'      => 'integer',
    ];

    protected $hidden = ['password'];

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPasswordAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function applyActive(): void
    {
        $config = static::where('is_active', true)->first();
        if (!$config) return;

        Config::set('mail.default', $config->mailer);
        Config::set('mail.mailers.smtp.host', $config->host);
        Config::set('mail.mailers.smtp.port', $config->port);
        Config::set('mail.mailers.smtp.username', $config->username);
        Config::set('mail.mailers.smtp.password', $config->password);
        Config::set('mail.mailers.smtp.encryption', $config->encryption === 'none' ? null : $config->encryption);
        Config::set('mail.from.address', $config->from_address);
        Config::set('mail.from.name', $config->from_name);
    }

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
