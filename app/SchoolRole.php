<?php

namespace App;

enum SchoolRole: string
{
    case SchoolAdmin = 'School Admin';
    case Teacher = 'Teacher';
    case Student = 'Student';
    case ParentGuardian = 'Parent/Guardian';
}
