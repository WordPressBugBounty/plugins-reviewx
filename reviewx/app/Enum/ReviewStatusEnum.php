<?php

namespace Rvx\Enum;

class ReviewStatusEnum
{
    const APPROVED = 1;
    const PENDING = 2;
    const TRASH = 3;
    const SPAM = 5;
    public static function getStatuses() : array
    {
        return [self::APPROVED, self::PENDING, self::TRASH, self::SPAM];
    }
}
