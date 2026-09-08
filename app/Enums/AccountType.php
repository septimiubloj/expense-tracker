<?php

namespace App\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';
    case Other = 'other';
}
