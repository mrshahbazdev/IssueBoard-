<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Developer = 'developer';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return __('app.roles.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('app.roles.'.$this->value.'.description');
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-50 text-violet-700 ring-violet-200',
            self::Manager => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Developer => 'bg-blue-50 text-blue-700 ring-blue-200',
            self::Member => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Viewer => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
    }
}
