<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CartService
{
    public const SESSION_KEY = 'cart';

    public const MIN_QUANTITY = 1;

    public const MAX_QUANTITY = 99;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        /** @var array<string, array<string, mixed>> $cart */
        $cart = Session::get(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }

    public function isEmpty(): bool
    {
        return $this->all() === [];
    }

    public function get(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Add a product to the cart. Merges when product + note match.
     *
     * @throws ValidationException
     */
    public function add(int $productId, int $quantity = 1, ?string $note = null): array
    {
        $product = $this->resolveOrderableProduct($productId);
        $quantity = $this->normalizeQuantity($quantity);
        $note = $this->normalizeNote($note);
        $key = $this->lineKey($product->id, $note);

        $cart = $this->all();

        if (isset($cart[$key])) {
            $newQuantity = $this->normalizeQuantity(((int) $cart[$key]['quantity']) + $quantity);
            $cart[$key]['quantity'] = $newQuantity;
            $cart[$key]['subtotal'] = Money::multiply($cart[$key]['price'], $newQuantity);
        } else {
            $price = bcadd((string) $product->price, '0', 2);

            $cart[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'note' => $note,
                'image' => $product->image,
                'subtotal' => Money::multiply($price, $quantity),
            ];
        }

        $this->put($cart);

        return $cart[$key];
    }

    /**
     * @throws ValidationException
     */
    public function update(string $key, ?int $quantity = null, ?string $note = null, bool $updateNote = false): array
    {
        $cart = $this->all();

        if (! isset($cart[$key])) {
            throw ValidationException::withMessages([
                'cart' => 'الصنف غير موجود في السلة.',
            ]);
        }

        $item = $cart[$key];
        $product = $this->resolveOrderableProduct((int) $item['product_id']);

        if ($quantity !== null) {
            $item['quantity'] = $this->normalizeQuantity($quantity);
        }

        $currentNote = (string) ($item['note'] ?? '');

        if ($updateNote) {
            $currentNote = $this->normalizeNote($note) ?? '';
            $item['note'] = $currentNote === '' ? null : $currentNote;
        }

        $price = bcadd((string) $product->price, '0', 2);
        $item['name'] = $product->name;
        $item['price'] = $price;
        $item['image'] = $product->image;
        $item['subtotal'] = Money::multiply($price, (int) $item['quantity']);

        $newKey = $this->lineKey($product->id, $item['note'] ?? null);

        unset($cart[$key]);

        if (isset($cart[$newKey]) && $newKey !== $key) {
            $mergedQuantity = $this->normalizeQuantity(
                ((int) $cart[$newKey]['quantity']) + ((int) $item['quantity'])
            );
            $item['quantity'] = $mergedQuantity;
            $item['subtotal'] = Money::multiply($price, $mergedQuantity);
        }

        $item['key'] = $newKey;
        $cart[$newKey] = $item;
        $this->put($cart);

        return $item;
    }

    /**
     * @throws ValidationException
     */
    public function remove(string $key): void
    {
        $cart = $this->all();

        if (! isset($cart[$key])) {
            throw ValidationException::withMessages([
                'cart' => 'الصنف غير موجود في السلة.',
            ]);
        }

        unset($cart[$key]);
        $this->put($cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function totalQuantity(): int
    {
        return (int) collect($this->all())->sum(fn (array $item) => (int) $item['quantity']);
    }

    public function subtotal(): string
    {
        return collect($this->all())->reduce(
            fn (string $carry, array $item) => Money::add($carry, (string) $item['subtotal']),
            '0.00'
        );
    }

    /**
     * Refresh cart lines against the live database.
     *
     * @return array{items: Collection<int, array<string, mixed>>, removed: list<string>, subtotal: string}
     *
     * @throws ValidationException
     */
    public function revalidate(bool $failOnUnavailable = true): array
    {
        $cart = $this->all();
        $refreshed = [];
        $removed = [];

        foreach ($cart as $key => $item) {
            $product = Product::query()
                ->with('category')
                ->find($item['product_id'] ?? null);

            if (! $product || ! $this->isOrderable($product)) {
                $removed[] = (string) ($item['name'] ?? 'صنف');
                continue;
            }

            $price = bcadd((string) $product->price, '0', 2);
            $quantity = $this->normalizeQuantity((int) ($item['quantity'] ?? 1));
            $note = $this->normalizeNote($item['note'] ?? null);
            $newKey = $this->lineKey($product->id, $note);

            if (isset($refreshed[$newKey])) {
                $quantity = $this->normalizeQuantity(
                    ((int) $refreshed[$newKey]['quantity']) + $quantity
                );
            }

            $refreshed[$newKey] = [
                'key' => $newKey,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'note' => $note,
                'image' => $product->image,
                'subtotal' => Money::multiply($price, $quantity),
            ];
        }

        if ($failOnUnavailable && ($removed !== [] || $refreshed === [])) {
            throw ValidationException::withMessages([
                'cart' => $removed !== []
                    ? 'أحد الأصناف الموجودة في طلبك لم يعد متاحاً.'
                    : 'سلتك فارغة أو لا تحتوي على أصناف متاحة.',
            ]);
        }

        $this->put($refreshed);

        $items = collect($refreshed)->values();

        return [
            'items' => $items,
            'removed' => $removed,
            'subtotal' => $items->reduce(
                fn (string $carry, array $item) => Money::add($carry, (string) $item['subtotal']),
                '0.00'
            ),
        ];
    }

    public function lineKey(int $productId, ?string $note): string
    {
        $normalized = $this->normalizeNote($note) ?? '';

        return $productId.':'.md5($normalized);
    }

    /**
     * @throws ValidationException
     */
    public function resolveOrderableProduct(int $productId): Product
    {
        $product = Product::query()
            ->with('category')
            ->find($productId);

        if (! $product || ! $this->isOrderable($product)) {
            throw ValidationException::withMessages([
                'product_id' => 'هذا الصنف غير متاح حالياً.',
            ]);
        }

        return $product;
    }

    public function isOrderable(Product $product): bool
    {
        return $product->is_available
            && $product->category
            && $product->category->is_active;
    }

    /**
     * @throws ValidationException
     */
    public function normalizeQuantity(int $quantity): int
    {
        if ($quantity < self::MIN_QUANTITY || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages([
                'quantity' => 'الكمية يجب أن تكون بين 1 و 99.',
            ]);
        }

        return $quantity;
    }

    public function normalizeNote(?string $note): ?string
    {
        if ($note === null) {
            return null;
        }

        $clean = trim(strip_tags($note));
        $clean = Str::of($clean)->replaceMatches('/\s+/u', ' ')->toString();

        if ($clean === '') {
            return null;
        }

        return mb_substr($clean, 0, 255);
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    protected function put(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
