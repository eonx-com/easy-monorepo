<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ParentModel extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'title',
    ];

    /**
     * @var string
     */
    protected $table = 'parents';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
