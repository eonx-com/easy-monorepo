<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Model;

use Illuminate\Database\Eloquent\Model;

final class Item extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
    ];
}
