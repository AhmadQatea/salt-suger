<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد التأكيد',
            self::Confirmed => 'تم التأكيد',
            self::Preparing => 'قيد التحضير',
            self::Ready => 'جاهز',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
        };
    }

    /**
     * CSS modifier used by admin status badges.
     */
    public function badgeModifier(): string
    {
        return match ($this) {
            self::Pending => 'is-pending',
            self::Confirmed => 'is-confirmed',
            self::Preparing => 'is-preparing',
            self::Ready => 'is-ready',
            self::Completed => 'is-completed',
            self::Cancelled => 'is-cancelled',
        };
    }

    /**
     * @return array<string, string> value => Arabic label
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    public static function tryFromInput(mixed $value): ?self
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
