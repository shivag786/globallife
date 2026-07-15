<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'status', 'mobile', 'commission_percentage', 'created_by'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'status', 'mobile', 'commission_percentage'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Cities this user serves (when role is commission_partner).
     *
     * @return BelongsToMany<City, $this>
     */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'city_manager')
            ->withPivot(['commission_type', 'commission_value'])
            ->withTimestamps();
    }

    /**
     * Cities this user is assigned as a branch (when role is branch_manager).
     *
     * @return BelongsToMany<City, $this>
     */
    public function branchCities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'branch_manager_cities')->withTimestamps();
    }

    /**
     * Commission Partners created by this Branch Manager.
     *
     * @return HasMany<User, $this>
     */
    public function commissionPartners(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * The Branch Manager who created this Commission Partner, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * VIP Members created by this Commission Partner. Same underlying FK as
     * commissionPartners() — named separately for the commission-partner-facing context.
     *
     * @return HasMany<User, $this>
     */
    public function vipMembers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * This user's public business microsite (when role is vip_member).
     *
     * @return HasOne<VipMicrosite, $this>
     */
    public function vipMicrosite(): HasOne
    {
        return $this->hasOne(VipMicrosite::class);
    }

    /**
     * @return HasOne<Wallet, $this>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * Product-sale commission earned by this user (as a beneficiary of the split).
     *
     * @return HasMany<CommissionEarning, $this>
     */
    public function commissionEarnings(): HasMany
    {
        return $this->hasMany(CommissionEarning::class, 'beneficiary_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Leads assigned to this user (when role is commission_partner).
     *
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_manager_id');
    }
}
