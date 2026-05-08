<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\DropImage;
use App\Models\Friendship;
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
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\SearchHistory;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const USER_COUNT = 200;

    private const USER_ROLES_COUNT = 400;

    private const STORE_COUNT = 80;

    private const PRODUCT_COUNT = 400;

    private const DROP_COUNT = 200;

    private const PRIZE_COUNT = 40;

    private const ORDER_COUNT = 220;

    private const ADVERTISEMENT_COUNT = 20;

    private const CONVERSATION_COUNT = 120;

    private const NOTIFICATION_COUNT = 500;

    private const LABEL_COUNT = 30;

    private const KEYWORDS_PER_LABEL = 40;

    /** @var array<int, int> */
    private array $roleIds = [];

    /** @var array<int, int> */
    private array $userIds = [];

    /** @var array<int, int> */
    private array $wilayaIds = [];

    /** @var array<int, int> */
    private array $categoryIds = [];

    /** @var array<int, int> */
    private array $genderIds = [];

    /** @var array<int, int> */
    private array $qualityIds = [];

    /** @var array<int, int> */
    private array $paymentMethodIds = [];

    /** @var array<int, int> */
    private array $socialPlatformIds = [];

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->cacheLookupIds();
            $this->seedLabelsAndKeywords();

            $users = $this->seedUsers();
            $this->seedUserRoles($users);
            $this->seedContacts($users);
            $this->seedFriendships($users);
            $this->seedCreatorFollowers($users);
            $wallets = $this->seedWallets($users);

            $stores = $this->seedStores($users);
            $products = $this->seedProducts($stores);
            $drops = $this->seedDrops($users, $products);
            $prizes = $this->seedPrizes($users);
            $orders = $this->seedOrders($users, $stores);

            $this->seedSavedItems($users, $products, $drops);
            $this->seedPrizeJoinings($users, $prizes);
            $this->seedWalletTransactions($wallets, $orders, $drops, $prizes);
            $this->seedAdvertisements();
            $this->seedSearchHistories($users);
            $this->seedConversations($users, $products, $drops);
            $this->seedNotifications($users, $orders, $drops, $prizes);
        });
    }

    private function cacheLookupIds(): void
    {
        dump('cacheLookupIds =================================');
        $this->roleIds = DB::table('roles')->pluck('id')->all();
        $this->userIds = DB::table('users')->pluck('id')->all();
        $this->wilayaIds = DB::table('wilayas')->pluck('id')->all();
        $this->categoryIds = DB::table('categories')->pluck('id')->all();
        $this->genderIds = DB::table('genders')->pluck('id')->all();
        $this->qualityIds = DB::table('qualities')->pluck('id')->all();
        $this->paymentMethodIds = DB::table('payment_methods')->pluck('id')->all();
        $this->socialPlatformIds = DB::table('social_platforms')->pluck('id')->all();
    }

    private function seedLabelsAndKeywords(): void
    {
        foreach (range(1, self::LABEL_COUNT) as $labelIndex) {
            $code = 'label_'.$labelIndex;

            $label = Label::query()->create([
                'code' => $code,
                'en' => Str::headline($code),
                'fr' => Str::headline($code),
            ]);

            foreach (range(1, self::KEYWORDS_PER_LABEL) as $keywordIndex) {
                Keyword::query()->create([
                    'label_id' => $label->id,
                    'code' => 'keyword_'.$labelIndex.'_'.$keywordIndex,
                ]);
            }
        }
    }

    private function seedUsers(): Collection
    {
        dump('seedUsers ============================');

        return collect(range(1, self::USER_COUNT))->map(function (int $index): User {
            $user = User::query()->create([
                'wilaya_id' => fake()->randomElement($this->wilayaIds),
                'full_name' => fake()->unique()->name(),
                'username' => fake()->unique()->userName(),
                'phone_number' => '055'.str_pad((string) $index, 7, '0', STR_PAD_LEFT),
                'phone_verified_at' => now()->subDays(random_int(1, 90)),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now()->subDays(random_int(1, 90)),
                'password' => 'password',
                'password_plaintext' => 'password',
                'image' => 'users/avatar-'.$index.'.jpg',
                'is_active' => true,
            ]);

            $user->roles()->attach(1);

            return $user;
        });
    }

    private function seedUserRoles(Collection $users): Collection
    {
        dump('seedUserRoles ============================');

        return collect(range(1, self::USER_ROLES_COUNT))->map(function () use ($users) {
            $user = $users->random();
            $roleId = fake()->randomElement($this->roleIds);

            $user->roles()->syncWithoutDetaching([$roleId]);

            return $user;
        });
    }

    private function seedContacts(Collection $users): void
    {
        dump('seedContacts =================================');
        foreach ($users as $user) {
            foreach (range(1, random_int(1, 2)) as $index) {
                Contact::query()->create([
                    'user_id' => $user->id,
                    'social_platform_id' => fake()->randomElement($this->socialPlatformIds),
                    'url' => fake()->url(),
                ]);
            }
        }
    }

    private function seedFriendships(Collection $users): void
    {
        $pairs = [];

        dump('seedFriendships =================================');
        foreach (range(1, max(40, intdiv($users->count(), 2))) as $index) {
            $sender = $users->random();
            $receiver = $users->where('id', '!=', $sender->id)->random();
            $pairKey = $sender->id.'-'.$receiver->id;

            if (isset($pairs[$pairKey])) {
                continue;
            }

            $pairs[$pairKey] = true;
            $status = collect(['pending', 'accepted', 'rejected'])->random();

            Friendship::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => $status,
                'accepted_at' => $status === 'accepted' ? now()->subDays(random_int(1, 30)) : null,
                'rejected_at' => $status === 'rejected' ? now()->subDays(random_int(1, 30)) : null,
            ]);
        }
    }

    private function seedCreatorFollowers(Collection $users): void
    {
        $pairs = [];

        dump('seedCreatorFollowers =================================');
        foreach (range(1, max(80, $users->count())) as $index) {
            $user = $users->random();
            $creator = $users->where('id', '!=', $user->id)->random();
            $pairKey = $user->id.'-'.$creator->id;

            if (isset($pairs[$pairKey])) {
                continue;
            }

            $pairs[$pairKey] = true;

            CreatorFollower::query()->create([
                'user_id' => $user->id,
                'creator_id' => $creator->id,
            ]);
        }
    }

    private function seedWallets(Collection $users): Collection
    {
        dump('seedWallets ============================');

        return $users->map(function (User $user): Wallet {
            return Wallet::query()->create([
                'user_id' => $user->id,
                'balance' => fake()->randomFloat(2, 0, 50000),
                'currency' => 'DZD',
            ]);
        });
    }

    private function seedStores(Collection $users): Collection
    {
        dump('seedStores ============================');

        return $users->shuffle()->take(min(self::STORE_COUNT, $users->count()))->values()->map(function (User $user, int $index): Store {
            return Store::query()->create([
                'user_id' => $user->id,
                'wilaya_id' => $user->wilaya_id,
                'store_name' => fake()->company().' Store',
                'phone_number' => $user->phone_number,
                'logo' => 'stores/logo-'.($index + 1).'.jpg',
                'description' => fake()->sentence(),
                'balance' => fake()->randomFloat(2, 0, 100000),
                'status' => collect(['pending', 'active', 'suspended'])->random(),
            ]);
        });
    }

    private function seedProducts(Collection $stores): Collection
    {
        $products = collect();

        dump('seedProducts =================================');
        foreach (range(1, self::PRODUCT_COUNT) as $index) {
            $store = $stores->random();
            $categoryId = fake()->randomElement($this->categoryIds);

            $originalPrice = fake()->randomFloat(2, 2000, 15000);
            $showPrice = fake()->randomFloat(2, 1500, $originalPrice);
            $storePrice = fake()->randomFloat(2, 1200, $showPrice);

            $product = Product::query()->create([
                'store_id' => $store->id,
                'category_id' => $categoryId,
                'gender_id' => fake()->randomElement($this->genderIds),
                'quality_id' => fake()->randomElement($this->qualityIds),
                'payment_method_id' => fake()->randomElement($this->paymentMethodIds),
                'name' => fake()->words(random_int(2, 4), true),
                'description' => fake()->paragraph(),
                'original_price' => $originalPrice,
                'show_price' => $showPrice,
                'store_price' => $storePrice,
                'status' => collect(['draft', 'published', 'archived'])->random(),
            ]);

            foreach (range(0, random_int(1, 4) - 1) as $imageIndex) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'image' => 'products/'.$product->id.'-'.$imageIndex.'.jpg',
                    'sort_order' => $imageIndex,
                    'is_main' => $imageIndex === 0,
                ]);
            }

            $sizeIds = DB::table('sizes')
                ->where('category_id', $categoryId)
                ->inRandomOrder()
                ->limit(random_int(1, 4))
                ->pluck('id');

            foreach ($sizeIds as $sizeId) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'size_id' => $sizeId,
                    'quantity' => random_int(1, 40),
                ]);
            }

            foreach (Keyword::query()->inRandomOrder()->limit(random_int(1, 4))->get() as $keyword) {
                ProductKeyword::query()->create([
                    'keyword_id' => $keyword->id,
                    'label_id' => $keyword->label_id,
                    'product_id' => $product->id,
                ]);
            }

            $products->push($product);
        }

        return $products;
    }

    private function seedDrops(Collection $users, Collection $products): Collection
    {
        $drops = collect();

        dump('seedDrops =================================');
        foreach (range(1, self::DROP_COUNT) as $index) {
            $drop = Drop::query()->create([
                'creator_id' => $users->random()->id,
                'title' => fake()->words(3, true),
                'description' => fake()->paragraph(),
                'status' => collect(['draft', 'published', 'ended', 'cancelled'])->random(),
            ]);

            foreach (range(0, random_int(1, 4) - 1) as $imageIndex) {
                DropImage::query()->create([
                    'drop_id' => $drop->id,
                    'image' => 'drops/'.$drop->id.'-'.$imageIndex.'.jpg',
                    'sort_order' => $imageIndex,
                    'is_main' => $imageIndex === 0,
                ]);
            }

            foreach ($products->shuffle()->take(random_int(2, 6)) as $product) {
                $drop->products()->attach($product->id, [
                    'drop_price' => fake()->randomFloat(2, 1000, 8000),
                ]);
            }

            $drops->push($drop);
        }

        return $drops;
    }

    private function seedPrizes(Collection $users): Collection
    {
        dump('seedPrizes ============================');

        return collect(range(1, self::PRIZE_COUNT))->map(function (int $index) use ($users): Prize {
            return Prize::query()->create([
                'creator_id' => $users->random()->id,
                'title' => 'Prize '.($index),
                'image' => 'prizes/'.$index.'.jpg',
                'description' => fake()->sentence(),
                'starts_at' => now()->subDays(random_int(1, 15)),
                'ends_at' => now()->addDays(random_int(5, 30)),
                'joining_price' => fake()->randomFloat(2, 0, 2000),
                'status' => collect(['draft', 'active', 'ended', 'cancelled'])->random(),
            ]);
        });
    }

    private function seedOrders(Collection $users, Collection $stores): Collection
    {
        dump('seedOrders ============================');

        return collect(range(1, self::ORDER_COUNT))->map(function (int $index) use ($users, $stores): Order {
            $store = $stores->random();
            $wilaya = Wilaya::query()->inRandomOrder()->first();
            $order = Order::query()->create([
                'wilaya_id' => $wilaya?->id,
                'user_id' => $users->random()->id,
                'store_id' => $store->id,
                'order_number' => 'ORD-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                'payment_method_id' => PaymentMethod::query()->inRandomOrder()->value('id'),
                'full_name' => fake()->name(),
                'phone_number' => fake()->numerify('055#######'),
                'wilaya' => $wilaya?->en ?? 'Algiers',
                'baladiya' => fake()->city(),
                'home_address' => fake()->address(),
                'delivery_method' => collect(['home', 'desk'])->random(),
                'delivery_fees' => fake()->randomFloat(2, 0, 1000),
                'subtotal' => 0,
                'total' => 0,
                'status' => collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->random(),
                'has_claim_issue' => fake()->boolean(10),
                'claim_issue' => fake()->boolean(10) ? fake()->sentence() : null,
            ]);

            $subtotal = 0;

            foreach (Product::query()->where('store_id', $store->id)->inRandomOrder()->limit(random_int(1, 3))->get() as $product) {
                $quantity = random_int(1, 3);
                $unitPrice = (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 1000);
                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? 'Product',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + (float) $order->delivery_fees,
            ]);

            return $order;
        });
    }

    private function seedSavedItems(Collection $users, Collection $products, Collection $drops): void
    {
        dump('seedSavedItems ============================');
        foreach ($users as $user) {
            foreach ($products->shuffle()->take(random_int(3, 12)) as $product) {
                SavedProduct::query()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }

            foreach ($drops->shuffle()->take(random_int(2, 8)) as $drop) {
                SavedDrop::query()->create([
                    'user_id' => $user->id,
                    'drop_id' => $drop->id,
                ]);
            }
        }
    }

    private function seedPrizeJoinings(Collection $users, Collection $prizes): void
    {
        dump('seedPrizeJoinings ============================');
        foreach ($prizes as $prize) {
            foreach ($users->shuffle()->take(random_int(5, 20)) as $user) {
                PrizeJoining::query()->create([
                    'prize_id' => $prize->id,
                    'user_id' => $user->id,
                    'amount_paid' => $prize->joining_price,
                    'status' => collect(['joined', 'winner', 'lost'])->random(),
                ]);
            }
        }
    }

    private function seedWalletTransactions(Collection $wallets, Collection $orders, Collection $drops, Collection $prizes): void
    {
        dump('seedWalletTransactions ============================');
        $relatedItems = collect([...$orders->all(), ...$drops->all(), ...$prizes->all()]);

        foreach ($wallets as $wallet) {
            foreach (range(1, random_int(2, 4)) as $index) {
                $related = $relatedItems->random();

                WalletTransaction::query()->create([
                    'wallet_id' => $wallet->id,
                    'type' => collect(['deposit', 'withdraw', 'purchase', 'refund', 'drop_sale', 'prize_joining'])->random(),
                    'amount' => fake()->randomFloat(2, 100, 5000),
                    'related_type' => $related::class,
                    'related_id' => $related->id,
                    'description' => fake()->sentence(),
                    'status' => collect(['pending', 'completed', 'failed', 'cancelled'])->random(),
                ]);
            }
        }
    }

    private function seedAdvertisements(): void
    {
        dump('seedAdvertisements ============================');
        foreach (range(1, self::ADVERTISEMENT_COUNT) as $index) {
            Advertisement::query()->create([
                'title' => 'Ad '.$index,
                'image' => 'ads/'.$index.'.jpg',
                'url' => fake()->url(),
                'description' => fake()->sentence(),
                'status' => collect(['draft', 'active', 'inactive'])->random(),
                'sort_order' => $index,
                'starts_at' => now()->subDays(random_int(1, 10)),
                'ends_at' => now()->addDays(random_int(5, 20)),
            ]);
        }
    }

    private function seedSearchHistories(Collection $users): void
    {
        dump('seedSearchHistories ============================');
        foreach ($users as $user) {
            foreach (range(1, random_int(3, 8)) as $index) {
                SearchHistory::query()->create([
                    'user_id' => $user->id,
                    'query' => fake()->words(2, true),
                    'type' => collect(['product', 'store', 'creator', 'general'])->random(),
                ]);
            }
        }
    }

    private function seedConversations(Collection $users, Collection $products, Collection $drops): void
    {
        $pairs = [];

        dump('seedConversations ============================');
        foreach (range(1, self::CONVERSATION_COUNT) as $index) {
            $participants = $users->shuffle()->take(2)->values();

            if ($participants->count() < 2) {
                continue;
            }

            $pair = [$participants[0]->id, $participants[1]->id];
            sort($pair);
            $pairKey = implode('-', $pair);

            if (isset($pairs[$pairKey])) {
                continue;
            }

            $pairs[$pairKey] = true;

            $conversation = Conversation::query()->create([
                'type' => collect(['private', 'support'])->random(),
                'first_user_id' => $participants[0]->id,
                'second_user_id' => $participants[1]->id,
                'first_user_last_read_at' => now()->subMinutes(random_int(1, 1440)),
                'second_user_last_read_at' => now()->subMinutes(random_int(1, 1440)),
            ]);

            foreach (range(1, random_int(2, 8)) as $messageIndex) {
                $messageType = collect(['text', 'product', 'profile', 'image'])->random();

                Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $participants->random()->id,
                    'type' => $messageType,
                    'body' => match ($messageType) {
                        'text' => fake()->sentence(),
                        'image' => fake()->imageUrl(),
                        default => null,
                    },
                    'attachable_type' => match ($messageType) {
                        'product' => Product::class,
                        'profile' => User::class,
                        default => null,
                    },
                    'attachable_id' => match ($messageType) {
                        'product' => $products->random()->id,
                        'profile' => $users->random()->id,
                        default => null,
                    },
                ]);
            }
        }
    }

    private function seedNotifications(Collection $users, Collection $orders, Collection $drops, Collection $prizes): void
    {
        $targets = collect([...$orders->all(), ...$drops->all(), ...$prizes->all()]);

        dump('seedNotifications ============================');
        foreach (range(1, self::NOTIFICATION_COUNT) as $index) {
            $notificationType = NotificationType::query()->inRandomOrder()->first();
            $user = $users->random();
            $notifiable = $targets->random();

            if ($notificationType === null) {
                continue;
            }

            Notification::query()->create([
                'notification_type_id' => $notificationType->id,
                'user_id' => $user->id,
                'notifiable_type' => $notifiable::class,
                'notifiable_id' => $notifiable->id,
                'data' => [
                    'title' => fake()->sentence(3),
                    'message' => fake()->sentence(),
                ],
                'read_at' => fake()->boolean(50) ? now()->subHours(random_int(1, 72)) : null,
            ]);
        }
    }
}
