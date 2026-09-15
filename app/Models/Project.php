<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\IssueBoard\Models\Issue;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'color'];

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }
}
