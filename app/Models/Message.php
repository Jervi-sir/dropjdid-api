<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'attachable_type',
        'attachable_id',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toMessageType(): array
    {
        $type = $this->type ?? 'text';
        $imageUrl = null;
        $profile = null;
        $product = null;
        $drop = null;
        $ad = null;
        $messageText = null;

        if ($type === 'text') {
            $messageText = $this->body;
        } elseif ($type === 'image') {
            $imageUrl = $this->body;
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }
        } elseif ($type === 'profile') {
            $user = $this->attachable instanceof User ? $this->attachable : User::find($this->attachable_id);
            if ($user) {
                $pImg = $user->image_url ?? '';
                if ($pImg && ! str_starts_with($pImg, 'http://') && ! str_starts_with($pImg, 'https://')) {
                    $pImg = url($pImg);
                }
                $profile = [
                    'id' => (int) $user->id,
                    'image_url' => (string) $pImg,
                    'text1' => (string) ($user->full_name ?? $user->name ?? $user->username),
                    'text2' => (string) ('@'.$user->username),
                ];
            }
        } elseif ($type === 'product') {
            $p = $this->attachable instanceof Product ? $this->attachable : Product::with(['mainImage', 'images'])->find($this->attachable_id);
            if ($p) {
                $pImg = $p->mainImage?->image_url ?? $p->images->first()?->image_url ?? '';
                if ($pImg && ! str_starts_with($pImg, 'http://') && ! str_starts_with($pImg, 'https://')) {
                    $pImg = url($pImg);
                }
                $product = [
                    'id' => (string) $p->id,
                    'image_url' => (string) $pImg,
                ];
            }
        } elseif ($type === 'drop') {
            $d = $this->attachable instanceof Drop ? $this->attachable : Drop::with(['mainImage', 'images'])->find($this->attachable_id);
            if ($d) {
                $dImg = $d->mainImage?->image ?? $d->images->first()?->image ?? '';
                if ($dImg && ! str_starts_with($dImg, 'http://') && ! str_starts_with($dImg, 'https://')) {
                    $dImg = url($dImg);
                }
                $drop = [
                    'id' => (string) $d->id,
                    'image_url' => (string) $dImg,
                ];
            }
        } elseif ($type === 'ad') {
            $a = $this->attachable instanceof Advertisement ? $this->attachable : Advertisement::find($this->attachable_id);
            if ($a) {
                $aImg = is_array($a->image) ? ($a->image[0] ?? '') : $a->image;
                if ($aImg && ! str_starts_with($aImg, 'http://') && ! str_starts_with($aImg, 'https://')) {
                    $aImg = url($aImg);
                }
                $ad = [
                    'id' => (string) $a->id,
                    'image_url' => (string) $aImg,
                ];
            }
        }

        return [
            'id' => (int) $this->id,
            'type' => (string) $type,
            'message' => $messageText,
            'image_url' => $imageUrl,
            'profile' => $profile,
            'product' => $product,
            'drop' => $drop,
            'ad' => $ad,
            'sender_id' => (int) $this->sender_id,
            'created_at' => $this->created_at,
        ];
    }
}
