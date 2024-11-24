<?php

namespace Rvx\Enum;

class OrderStatusEnum
{
    const PROCESSING = 'processing';
    const PENDING_PAYMENT = 'pending_payment';
    const ON_HOLD = 'on_hold';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';
    const DRAFT = 'draft';
    public static function getStatuses() : array
    {
        return [self::PROCESSING, self::PENDING_PAYMENT, self::ON_HOLD, self::COMPLETED, self::CANCELLED, self::REFUNDED, self::FAILED, self::DRAFT];
    }
}
