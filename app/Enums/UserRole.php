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
        return match ($this) {
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::Developer => 'Developer',
            self::Member => 'Team member',
            self::Viewer => 'Viewer',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Full access, including team roles.',
            self::Manager => 'Manages tasks, priorities, and workflow.',
            self::Developer => 'Works on tasks and moves implementation forward.',
            self::Member => 'Reports tasks, joins discussions, and updates own reports.',
            self::Viewer => 'Read-only access to the board and conversations.',
        };
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
