<?php

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\DropProduct;
use App\Models\Friendship;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Prize;
use App\Models\PrizeJoining;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductKeyword;
use App\Models\ProductVariant;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Save;
use App\Models\SearchHistory;
use App\Models\Size;
use App\Models\SocialPlatform;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

it('formats every application model as an array', function (string $modelClass): void {
    $model = new $modelClass;

    expect($model->format())->toBeArray();
})->with([
    Advertisement::class,
    Category::class,
    Contact::class,
    Conversation::class,
    ConversationParticipant::class,
    CreatorFollower::class,
    Drop::class,
    DropProduct::class,
    Friendship::class,
    Gender::class,
    Keyword::class,
    Label::class,
    Message::class,
    Notification::class,
    NotificationType::class,
    Order::class,
    OrderItem::class,
    PaymentMethod::class,
    Prize::class,
    PrizeJoining::class,
    Product::class,
    ProductImage::class,
    ProductKeyword::class,
    ProductVariant::class,
    Quality::class,
    Role::class,
    Save::class,
    SearchHistory::class,
    Size::class,
    SocialPlatform::class,
    Store::class,
    User::class,
    Wallet::class,
    WalletTransaction::class,
]);

it('exposes the expected relationship contracts', function (string $modelClass, string $method, string $relationClass): void {
    $model = new $modelClass;

    expect($model->{$method}())->toBeInstanceOf($relationClass);
})->with([
    [Category::class, 'sizes', HasMany::class],
    [Category::class, 'products', HasMany::class],
    [Contact::class, 'user', BelongsTo::class],
    [Contact::class, 'socialPlatform', BelongsTo::class],
    [Conversation::class, 'participants', HasMany::class],
    [Conversation::class, 'messages', HasMany::class],
    [Conversation::class, 'users', BelongsToMany::class],
    [ConversationParticipant::class, 'conversation', BelongsTo::class],
    [ConversationParticipant::class, 'user', BelongsTo::class],
    [CreatorFollower::class, 'user', BelongsTo::class],
    [CreatorFollower::class, 'creator', BelongsTo::class],
    [Drop::class, 'creator', BelongsTo::class],
    [Drop::class, 'products', BelongsToMany::class],
    [Drop::class, 'saves', MorphMany::class],
    [DropProduct::class, 'drop', BelongsTo::class],
    [DropProduct::class, 'product', BelongsTo::class],
    [Friendship::class, 'sender', BelongsTo::class],
    [Friendship::class, 'receiver', BelongsTo::class],
    [Gender::class, 'products', HasMany::class],
    [Keyword::class, 'label', BelongsTo::class],
    [Keyword::class, 'productKeywords', HasMany::class],
    [Keyword::class, 'products', BelongsToMany::class],
    [Label::class, 'keywords', HasMany::class],
    [Message::class, 'conversation', BelongsTo::class],
    [Message::class, 'sender', BelongsTo::class],
    [Message::class, 'attachable', MorphTo::class],
    [Notification::class, 'notificationType', BelongsTo::class],
    [Notification::class, 'user', BelongsTo::class],
    [Notification::class, 'notifiable', MorphTo::class],
    [NotificationType::class, 'notifications', HasMany::class],
    [Order::class, 'user', BelongsTo::class],
    [Order::class, 'store', BelongsTo::class],
    [Order::class, 'paymentMethod', BelongsTo::class],
    [Order::class, 'items', HasMany::class],
    [Order::class, 'conversations', HasMany::class],
    [OrderItem::class, 'order', BelongsTo::class],
    [OrderItem::class, 'product', BelongsTo::class],
    [PaymentMethod::class, 'products', HasMany::class],
    [PaymentMethod::class, 'orders', HasMany::class],
    [Prize::class, 'creator', BelongsTo::class],
    [Prize::class, 'joinings', HasMany::class],
    [Prize::class, 'saves', MorphMany::class],
    [PrizeJoining::class, 'prize', BelongsTo::class],
    [PrizeJoining::class, 'user', BelongsTo::class],
    [Product::class, 'store', BelongsTo::class],
    [Product::class, 'category', BelongsTo::class],
    [Product::class, 'quality', BelongsTo::class],
    [Product::class, 'paymentMethod', BelongsTo::class],
    [Product::class, 'gender', BelongsTo::class],
    [Product::class, 'images', HasMany::class],
    [Product::class, 'variants', HasMany::class],
    [Product::class, 'productKeywords', HasMany::class],
    [Product::class, 'orderItems', HasMany::class],
    [Product::class, 'keywords', BelongsToMany::class],
    [Product::class, 'drops', BelongsToMany::class],
    [Product::class, 'saves', MorphMany::class],
    [ProductImage::class, 'product', BelongsTo::class],
    [ProductKeyword::class, 'keyword', BelongsTo::class],
    [ProductKeyword::class, 'product', BelongsTo::class],
    [ProductVariant::class, 'product', BelongsTo::class],
    [ProductVariant::class, 'size', BelongsTo::class],
    [Quality::class, 'products', HasMany::class],
    [Role::class, 'users', HasMany::class],
    [Save::class, 'user', BelongsTo::class],
    [Save::class, 'saveable', MorphTo::class],
    [SearchHistory::class, 'user', BelongsTo::class],
    [Size::class, 'category', BelongsTo::class],
    [Size::class, 'variants', HasMany::class],
    [SocialPlatform::class, 'contacts', HasMany::class],
    [Store::class, 'user', BelongsTo::class],
    [Store::class, 'products', HasMany::class],
    [Store::class, 'orders', HasMany::class],
    [User::class, 'role', BelongsTo::class],
    [User::class, 'contacts', HasMany::class],
    [User::class, 'stores', HasMany::class],
    [User::class, 'wallets', HasMany::class],
    [User::class, 'searchHistories', HasMany::class],
    [User::class, 'orders', HasMany::class],
    [User::class, 'prizeJoinings', HasMany::class],
    [User::class, 'saves', HasMany::class],
    [User::class, 'drops', HasMany::class],
    [User::class, 'prizes', HasMany::class],
    [User::class, 'sentFriendships', HasMany::class],
    [User::class, 'receivedFriendships', HasMany::class],
    [User::class, 'sentMessages', HasMany::class],
    [User::class, 'conversationParticipants', HasMany::class],
    [User::class, 'conversations', BelongsToMany::class],
    [User::class, 'followedCreators', BelongsToMany::class],
    [User::class, 'followers', BelongsToMany::class],
    [User::class, 'notifications', HasMany::class],
    [Wallet::class, 'user', BelongsTo::class],
    [Wallet::class, 'transactions', HasMany::class],
    [WalletTransaction::class, 'wallet', BelongsTo::class],
    [WalletTransaction::class, 'related', MorphTo::class],
]);
