<?php

namespace Modules\IssueBoard\Enums;

enum IssueStatus: string
{
    case New = 'new';
    case Review = 'review';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return __('issueboard::issueboard.status.'.$this->value);
    }

    /** Hex value used for the column rail and the card dot. */
    public function color(): string
    {
        return match ($this) {
            self::New => '#dc2626',
            self::Review => '#d97706',
            self::InProgress => '#2563eb',
            self::Done => '#16a34a',
        };
    }

    /** Tint used behind the badge, so the dot colour stays readable. */
    public function tint(): string
    {
        return match ($this) {
            self::New => '#fef2f2',
            self::Review => '#fffbeb',
            self::InProgress => '#eff6ff',
            self::Done => '#f0fdf4',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Done;
    }

    /** Statuses that count as "needs attention" in the digest mail. */
    public static function needsAttention(): array
    {
        return [self::New, self::Review];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
