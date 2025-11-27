<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property-read string $role
 * @property-read ?\Spatie\Permission\Models\Role $latestRole
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static Builder<static>|User byEmail(string $email, bool $returnQuery = false)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User permission($permissions, $without = false)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereRole($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User withoutPermission($permissions)
 * @method static Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasEvents;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
        'latestRole',
    ];

    protected array $tempAttributes = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();
        // saving -> creating

        static::creating(function ($model) {
            $_role = $model->getTempAttribute('_role', $model->role ?? null);
            $role = $model->getTempAttribute('role', $_role);
            $model->putTempAttribute('role', $role);
            $model->putTempAttribute('_role', $_role ?? $role);
            unset($model->role, $model->_role);
        });

        static::created(function ($model) {
            $roles = array_filter(
                array_unique([
                    $model->getTempAttribute('role'),
                    $model->getTempAttribute('_role', $model->role ?? 'user'),
                ])
            );

            if ($roles) {
                $model->syncRoles($roles);
            }

            unset($model->role, $model->_role);
        });

        static::saving(function (&$model) {
            // $model->role = 'teste';
            // $model->_algo = 'teste algo';
            // $model->_this_key_exists_on_db = 'valor';

            $defaultTempKeys = [
                '_role',
                'role',
            ];

            $exceptKeys = [
                // '_this_key_exists_on_db',
            ];

            $model->attributes ??= [];

            foreach ($model->attributes as $key => $value) {
                if (!in_array($key, $exceptKeys) && (in_array($key, $defaultTempKeys) || str_starts_with($key, '_'))) {
                    $model->putTempAttribute($key, $value);
                    unset($model->attributes[$key]);

                    continue;
                }
            }

            // dd($model->getTempAttributes(), $model->attributes);

            if ($model->role === 'user') {
                $model->role = null;
                unset($model->role);

                return $model;
            }

            if ($model->role || $model->_role) {
                $model->putTempAttribute('role', $model->role ?: $model->_role);
                $model->putTempAttribute('_role', $model->_role ?: $model->role);

                $model->role = null;
                unset($model->role);
            }
        });
    }

    public function getTempAttribute(string $key, $default = null): mixed
    {
        return \Arr::get($this->getTempAttributes(), $key, $default);
    }

    public function putTempAttribute(string $key, mixed $value): array
    {
        $this->tempAttributes[$key] = $value;

        return $this->getTempAttributes();
    }

    public function removeTempAttribute(string $key): array
    {
        unset($this->tempAttributes[$key]);

        return $this->getTempAttributes();
    }

    public function getTempAttributes(): array
    {
        return $this->tempAttributes ?? [];
    }

    public function getRoleAttribute(): ?string
    {
        return $this->getLatestRole()?->name ?? 'user';
    }

    public function getLatestRoleAttribute(): ?\Spatie\Permission\Models\Role
    {
        return $this->getLatestRole();
    }

    public function getLatestRole(): ?\Spatie\Permission\Models\Role
    {
        return $this->roles()->latest()->first();
    }

    /**
     * Check if user has admin role (in users table).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has super-admin Spatie role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user has admin Spatie role.
     */
    public function isAdminRole(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user has manager Spatie role.
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user has staff Spatie role.
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->can('users.view') || $this->isSuperAdmin();
    }

    /**
     * Check if user can manage products.
     */
    public function canManageProducts(): bool
    {
        return $this->can('products.view') || $this->isSuperAdmin();
    }

    /**
     * Check if user can view dashboard.
     */
    public function canViewDashboard(): bool
    {
        return $this->can('dashboard.view') || $this->isSuperAdmin();
    }

    public function scopeByEmail(Builder $query, string $email, bool $returnQuery = false): null|Builder|User
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);

        $query->where('email', $email);

        if (!$returnQuery) {
            return $query->first();
        }

        return $query;
    }

    public function updatePassword(string $password): bool
    {
        throw_if(strlen($password) < 6, __('validation.gt.string', ['attribute' => __('password'), 'value' => 6]));

        $password = Hash::make($password);

        return (bool) $this->update([
            'password' => $password,
        ]);
    }
}
