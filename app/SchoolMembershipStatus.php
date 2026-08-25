<?php

namespace App;

enum SchoolMembershipStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Removed = 'removed';
}
