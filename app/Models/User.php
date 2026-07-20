<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'role',
        'profile_picture',
        'activity_last_seen_at',
        'notification_last_seen_at',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activity_last_seen_at' => 'datetime',
            'notification_last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AdminActivity::class);
    }

    public function fullName(): string
    {
        return trim("{$this->fname} {$this->lname}");
    }

    public function initials(): string
    {
        return strtoupper(mb_substr((string) $this->fname, 0, 1).mb_substr((string) $this->lname, 0, 1));
    }

    public function profilePictureUrl(): ?string
    {
        return $this->profile_picture ? asset($this->profile_picture) : null;
    }
}
