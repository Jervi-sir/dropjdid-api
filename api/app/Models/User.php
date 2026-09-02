<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int|null $wilaya_id
 * @property string|null $name
 * @property string|null $full_name
 * @property string|null $username
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $phone_number
 * @property Carbon|null $phone_verified_at
 * @property string $password
 * @property string|null $image_url
 * @property bool $is_active
 * @property string|null $user_status
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'wilaya_id', 'full_name', 'username', 'email', 'phone_number', 'password', 'image_url', 'is_active', 'user_status'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'password_plaintext'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['name', 'avatar'];

    /**
     * Get the user's display name.
     */
    public function getNameAttribute(): string
    {
        return (string) ($this->full_name ?: $this->username ?: $this->email);
    }

    /**
     * Set the user's display name.
     */
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['full_name'] = $value;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->image_url;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * User roles pivot records.
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Assigned roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Friend requests sent by this user.
     */
    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Friend requests received by this user.
     */
    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Friends where this user sent the request.
     */
    public function friendsOfMine(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
            ->wherePivot('status', Friendship::STATUS_ACCEPTED)
            ->withTimestamps();
    }

    /**
     * Friends where other user sent the request to this user.
     */
    public function friendOf(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')
            ->wherePivot('status', Friendship::STATUS_ACCEPTED)
            ->withTimestamps();
    }

    /**
     * All accepted friends merged together.
     */
    public function getFriendsAttribute()
    {
        return $this->friendsOfMine->merge($this->friendOf);
    }

    /**
     * Send a friend request to another user.
     */
    public function sendFriendRequest(int $friendId): Friendship
    {
        return Friendship::firstOrCreate(
            ['user_id' => $this->id, 'friend_id' => $friendId],
            ['status' => Friendship::STATUS_PENDING]
        );
    }

    /**
     * Accept an incoming friend request from a user.
     */
    public function acceptFriendRequest(int $senderUserId): bool
    {
        return (bool) Friendship::where('user_id', $senderUserId)
            ->where('friend_id', $this->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->update(['status' => Friendship::STATUS_ACCEPTED]);
    }

    /**
     * Decline an incoming friend request.
     */
    public function declineFriendRequest(int $senderUserId): bool
    {
        return (bool) Friendship::where('user_id', $senderUserId)
            ->where('friend_id', $this->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->delete(); // or ->update(['status' => Friendship::STATUS_DECLINED])
    }

    /**
     * Cancel an outgoing friend request.
     */
    public function cancelFriendRequest(int $friendId): bool
    {
        return (bool) Friendship::where('user_id', $this->id)
            ->where('friend_id', $friendId)
            ->where('status', Friendship::STATUS_PENDING)
            ->delete();
    }

    /**
     * Unfriend / remove friendship with a user.
     */
    public function unfriend(int $friendId): bool
    {
        return (bool) Friendship::where(function ($query) use ($friendId) {
            $query->where('user_id', $this->id)->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($friendId) {
            $query->where('user_id', $friendId)->where('friend_id', $this->id);
        })->delete();
    }

    /**
     * Stores owned by this user.
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Contacts belonging to this user.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }


    /**
     * Check if user is friends with another user.
     */
    public function isFriendsWith(int $userId): bool
    {
        return Friendship::where(function ($query) use ($userId) {
            $query->where('user_id', $this->id)->where('friend_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('friend_id', $this->id);
        })->where('status', Friendship::STATUS_ACCEPTED)->exists();
    }

    /**
     * Check if a pending friend request has been sent to a user.
     */
    public function hasSentFriendRequestTo(int $userId): bool
    {
        return Friendship::where('user_id', $this->id)
            ->where('friend_id', $userId)
            ->where('status', Friendship::STATUS_PENDING)
            ->exists();
    }

    /**
     * Check if a pending friend request has been received from a user.
     */
    public function hasReceivedFriendRequestFrom(int $userId): bool
    {
        return Friendship::where('user_id', $userId)
            ->where('friend_id', $this->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->exists();
    }

    /**
     * Structure user data uniformly for API responses.
     *
     * @return array<string, mixed>
     */
    public function toAuthArray(): array
    {
        $this->loadMissing('roles');

        return [
            'id' => $this->id,
            'username' => $this->username,
            'full_name' => $this->full_name ?? $this->name,
            'name' => $this->name ?? $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'phone_verified_at' => $this->phone_verified_at,
            'email_verified_at' => $this->email_verified_at,
            'image_url' => $this->image_url,
            'is_active' => (bool) $this->is_active,
            'user_status' => $this->user_status,
            'wilaya_id' => $this->wilaya_id,
            'user_roles' => $this->roles->map(fn ($role) => [
                'id' => $role->id,
                'code' => $role->code,
                'en' => $role->en,
                'fr' => $role->fr,
                'ar' => $role->ar,
            ])->values(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
