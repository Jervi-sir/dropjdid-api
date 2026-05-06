<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Contact;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\DropImage;
use App\Models\Friendship;
use App\Models\Keyword;
use App\Models\Label;
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
use App\Models\SearchHistory;
use App\Models\SocialPlatform;
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
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedLabelsAndKeywords();

            $users = $this->seedUsers();
            $this->seedContacts($users);
            $this->seedFriendships($users);
            $this->seedCreatorFollowers($users);
            $wallets = $this->seedWallets($users);

            $stores = $this->seedStores($users);
            $products = $this->seedProducts($stores);
            $drops = $this->seedDrops($users, $products);
            $prizes = $this->seedPrizes($users);
            $orders = $this->seedOrders($users, $stores);

            $this->seedPrizeJoinings($users, $prizes);
            $this->seedWalletTransactions($wallets, $orders, $drops, $prizes);
            $this->seedAdvertisements();
            $this->seedSearchHistories($users);
            $this->seedNotifications($users, $orders, $drops, $prizes);
        });
    }

    private function seedLabelsAndKeywords(): void
    {
        $labels = [
            'style' => ['casual', 'formal', 'sport', 'streetwear'],
            'season' => ['summer', 'winter', 'spring', 'autumn'],
            'material' => ['cotton', 'leather', 'denim', 'linen'],
            'color' => ['black', 'white', 'beige', 'pink'],
        ];

        foreach ($labels as $code => $keywords) {
            $label = Label::query()->create([
                'code' => $code,
                'en' => Str::headline($code),
                'fr' => Str::headline($code),
            ]);

            foreach ($keywords as $keywordCode) {
                Keyword::query()->create([
                    'label_id' => $label->id,
                    'code' => $keywordCode,
                ]);
            }
        }
    }

    private function seedUsers(): Collection
    {
        return collect(range(1, 12))->map(function (int $index): User {
            return User::query()->create([
                'role_id' => DB::table('roles')->inRandomOrder()->value('id'),
                'wilaya_id' => DB::table('wilayas')->inRandomOrder()->value('id'),
                'username' => fake()->unique()->userName(),
                'phone_number' => sprintf('055000%04d', $index),
                'phone_verified_at' => now()->subDays(random_int(1, 90)),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now()->subDays(random_int(1, 90)),
                'password' => 'password123',
                'image' => 'users/avatar-'.$index.'.jpg',
                'is_active' => true,
            ]);
        });
    }

    private function seedContacts(Collection $users): void
    {
        foreach ($users as $user) {
            foreach (range(1, random_int(1, 2)) as $index) {
                Contact::query()->create([
                    'user_id' => $user->id,
                    'social_platform_id' => SocialPlatform::query()->inRandomOrder()->value('id'),
                    'url' => fake()->url(),
                ]);
            }
        }
    }

    private function seedFriendships(Collection $users): void
    {
        $pairs = [];

        foreach (range(1, 14) as $index) {
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

        foreach (range(1, 16) as $index) {
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
        return $users->shuffle()->take(6)->map(function (User $user, int $index): Store {
            return Store::query()->create([
                'user_id' => $user->id,
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

        foreach ($stores as $store) {
            foreach (range(1, 4) as $index) {
                $categoryId = DB::table('categories')->inRandomOrder()->value('id');

                $product = Product::query()->create([
                    'store_id' => $store->id,
                    'category_id' => $categoryId,
                    'gender_id' => DB::table('genders')->inRandomOrder()->value('id'),
                    'quality_id' => DB::table('qualities')->inRandomOrder()->value('id'),
                    'payment_method_id' => DB::table('payment_methods')->inRandomOrder()->value('id'),
                    'name' => fake()->words(3, true),
                    'description' => fake()->sentence(),
                    'original_price' => fake()->randomFloat(2, 2000, 12000),
                    'show_price' => fake()->randomFloat(2, 1500, 10000),
                    'store_price' => fake()->randomFloat(2, 1200, 9000),
                    'status' => collect(['draft', 'published', 'archived'])->random(),
                ]);

                foreach (range(0, random_int(1, 3) - 1) as $imageIndex) {
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'image' => 'products/'.$product->id.'-'.$imageIndex.'.jpg',
                        'sort_order' => $imageIndex,
                        'is_main' => $imageIndex === 0,
                    ]);
                }

                $sizeIds = DB::table('sizes')->where('category_id', $categoryId)->inRandomOrder()->limit(random_int(1, 3))->pluck('id');
                foreach ($sizeIds as $sizeId) {
                    ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'size_id' => $sizeId,
                        'quantity' => random_int(1, 20),
                    ]);
                }

                foreach (Keyword::query()->inRandomOrder()->limit(random_int(1, 3))->get() as $keyword) {
                    ProductKeyword::query()->create([
                        'keyword_id' => $keyword->id,
                        'label_id' => $keyword->label->id,
                        'product_id' => $product->id,
                    ]);
                }

                $products->push($product);
            }
        }

        return $products;
    }

    private function seedDrops(Collection $users, Collection $products): Collection
    {
        $drops = collect();

        foreach (range(1, 8) as $index) {
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

            foreach ($products->shuffle()->take(random_int(2, 4)) as $product) {
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
        return collect(range(1, 6))->map(function (int $index) use ($users): Prize {
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
        return collect(range(1, 10))->map(function (int $index) use ($users, $stores): Order {
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
                'status' => collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->random(),
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

    private function seedPrizeJoinings(Collection $users, Collection $prizes): void
    {
        foreach ($prizes as $prize) {
            foreach ($users->shuffle()->take(random_int(2, 5)) as $user) {
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
        foreach (range(1, 5) as $index) {
            Advertisement::query()->create([
                'title' => 'Ad '.$index,
                'image' => 'ads/'.$index.'.jpg',
                'url' => fake()->url(),
                'status' => collect(['draft', 'active', 'inactive'])->random(),
                'sort_order' => $index,
                'starts_at' => now()->subDays(random_int(1, 10)),
                'ends_at' => now()->addDays(random_int(5, 20)),
            ]);
        }
    }

    private function seedSearchHistories(Collection $users): void
    {
        foreach ($users as $user) {
            foreach (range(1, random_int(2, 4)) as $index) {
                SearchHistory::query()->create([
                    'user_id' => $user->id,
                    'query' => fake()->words(2, true),
                    'type' => collect(['product', 'store', 'creator', 'general'])->random(),
                ]);
            }
        }
    }

    private function seedNotifications(Collection $users, Collection $orders, Collection $drops, Collection $prizes): void
    {
        $targets = collect([...$orders->all(), ...$drops->all(), ...$prizes->all()]);

        foreach (range(1, 16) as $index) {
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
