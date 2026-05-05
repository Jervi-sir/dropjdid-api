<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use FormatsModel, HasApiTokens, HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'role_id',
        'username',
        'phone_number',
        'phone_verified_at',
        'email',
        'email_verified_at',
        'password',
        'password_plaintext',
        'image',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'is_active',
        'remember_token',
    ];

    protected $hidden = ['password', 'password_plaintext', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : strtolower($value),
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function prizeJoinings(): HasMany
    {
        return $this->hasMany(PrizeJoining::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(Save::class);
    }

    public function drops(): HasMany
    {
        return $this->hasMany(Drop::class, 'creator_id');
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class, 'creator_id');
    }

    public function sentFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function followedCreators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'creator_followers', 'user_id', 'creator_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'creator_followers', 'creator_id', 'user_id')->withTimestamps();
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    protected function formatterRelations(): array
    {
        return ['role', 'contacts', 'stores', 'wallets', 'searchHistories', 'orders', 'prizeJoinings', 'saves', 'drops', 'prizes', 'sentFriendships', 'receivedFriendships', 'sentMessages', 'conversationParticipants', 'conversations', 'followedCreators', 'followers', 'notifications'];
    }
}
