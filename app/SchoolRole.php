<?php

namespace App;

enum SchoolRole: string
{
    case SchoolAdmin = 'School Admin';
    case Teacher = 'Teacher';
    case Student = 'Student';
    case ParentGuardian = 'Parent/Guardian';

    public function label(): string
    {
        return match ($this) {
            self::SchoolAdmin => __('School Admin'),
            self::Teacher => __('Teacher'),
            self::Student => __('Student'),
            self::ParentGuardian => __('Parent / Guardian'),
        };
    }
}
