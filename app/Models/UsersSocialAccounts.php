<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * Class UsersSocialAccounts
 * @package App\Models
 */
class UsersSocialAccounts extends Model
{
    /** @var array $fillable */
    protected $fillable = [
        'provider', 'provider_user_id', 'user_id', 'nickname'
    ];

    /**
     * Relation with the user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @param string $provider
     * @param ProviderUser $providerUser
     * @return mixed
     */
    public static function findByProviderAndUserId(string $provider, ProviderUser $providerUser)
    {
        $providerUserId = $providerUser->getId();

        return self::where([
            ['provider', '=', $provider],
            ['provider_user_id', '=', $providerUserId],
        ])
            ->first();
    }
}
