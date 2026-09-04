<?php

namespace App;

enum SchoolMembershipStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Suspended => __('Suspended'),
            self::Removed => __('Removed'),
        };
    }
}
