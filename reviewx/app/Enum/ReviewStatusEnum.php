<?php

namespace Rvx\Enum;

class ReviewStatusEnum
{
    const APPROVED = 1;
    const UNPUBLISHED = 2;
    const TRASH = 3;
    const PENDING = 4;
    const SPAM = 5;
    const ANY = 6;
    const ARCHIVE = 7;
    public static function getStatuses() : array
    {
        return [self::APPROVED, self::PENDING, self::TRASH, self::SPAM];
    }
}
