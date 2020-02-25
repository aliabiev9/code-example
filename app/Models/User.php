<?php

namespace App\Models;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Traits\Picturable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * @package App
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property string $date_of_birth
 * @property string $gender
 * @property-read \App\Models\Picture $picture
 * @property string|null $email_confirmed
 * @property string $role
 * @property string $password
 *
 * @property Collection $trainingCategories
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, Picturable, SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->password = bcrypt($model->password);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'address', 'gender', 'date_of_birth', 'secure_code'
    ];

    /** @var array $attributes -  attributes with default values */
    protected $attributes = [
        'phone' => null,
        'address' => '',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relation to social Accounts auth info
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(UsersSocialAccounts::class);
    }

    /**
     * @param string $role
     * @return bool
     */
    public function isRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role == config('app.role_admin');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relation with the Training Categories selected by User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function trainingCategories(): BelongsToMany
    {
        return $this->belongsToMany(TrainingCategory::class, 'training_category_user',
            'user_id', 'training_category_id');
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function createUserByEmail(array $data): User
    {
        $user = static::create(array_merge($data, [
            'name' => $data['name'],
            'address' => $data['address'],
            'email' => $data['email'],
            'role' => config('app.role_user'),
            'password' => $data['password'],
        ]));
        return $user;
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function createUserByPhone(array $data): User
    {
        $user = static::create(array_merge($data, [
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'role' => config('app.role_user'),
            'password' => $data['password'],
        ]));
        return $user;
    }

    /**
     * Find User by email
     * @param $email
     * @return mixed
     */
    public static function getUserByEmail($email)
    {
        return self::where('email', '=', $email)->first();
    }

    /**
     * Get default password for users authenticated with Social Media
     * @return mixed
     */
    public static function getDefaultSocialMediaPassword()
    {
        return env('SOCIAL_AUTH_DEFAULT_PASSWORD');
    }

    /**
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request): User
    {
        $user = $this->firstOrCreate($request->except('picture'));

        $user->saveImageIfExist($request, $user);

        return $user;
    }

    /**
     * @param UserUpdateRequest $request
     */
    public function updateUser(UserUpdateRequest $request)
    {
        $this->update($request->except('password'));
        if ($request->password) {
            $this->password = bcrypt($request->password);
            $this->save();
        }
    }

    public function saveImageIfExist(Request $request, User $user)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($user->picture) {
                $user->picture->delete();
            }
            $user->saveImage($request->picture, 'Avatar');
        }
    }

    /**
     * @param null $search
     * @return User|Builder
     */
    public static function getUsers($search = null)
    {
        $query = User::orderBy('created_at', 'desc')
            ->with('picture', 'socialAccounts');

        if ($search) {
            $query->where(function (Builder $query) use ($search) {
                $query->orWhere('name',  'like', "%{$search}%");
                $query->orWhere('email', 'like', "%{$search}%");
                $query->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * @param UpdatePasswordRequest $request
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->password = bcrypt($request->new_password);
        $this->save();
    }

    public function updateSecureCode()
    {
        $this->secure_code = mt_rand(1000000000, 9999999999);
        $this->save();
    }
}
