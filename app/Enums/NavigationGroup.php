<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum NavigationGroup implements HasLabel, HasIcon
{
    case PRODUCT_MANAGEMENT;
    case USER_MANAGEMENT;
    case SYSTEM;

    public function getLabel(): string
    {
        return match ($this) {
            self::PRODUCT_MANAGEMENT => 'Product Management',
            self::USER_MANAGEMENT => 'User Management',
            self::SYSTEM => 'System',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PRODUCT_MANAGEMENT => 'heroicon-o-shopping-bag',
            self::USER_MANAGEMENT => 'heroicon-o-user-group',
            self::SYSTEM => 'heroicon-o-cog',
        };
    }
}
