<?php

namespace BagistoPlus\Visual\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VisualTemplateAssignment extends Model
{
    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'template_type',
        'template_key',
        'channel',
        'locale',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
