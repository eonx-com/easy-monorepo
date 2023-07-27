<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChildItemModel extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'child_title',
        'item_id',
    ];

    /**
     * @var string
     */
    protected $table = 'child_items';

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemModel::class, 'item_id', 'id');
    }
}
