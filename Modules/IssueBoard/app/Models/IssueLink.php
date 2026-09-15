<?php

namespace Modules\IssueBoard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueLink extends Model
{
    protected $fillable = ['label', 'url'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: parse_url($this->url, PHP_URL_HOST) ?: $this->url;
    }
}
