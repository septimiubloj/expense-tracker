<?php

namespace App\Enums;

enum BudgetPeriodStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';
}
