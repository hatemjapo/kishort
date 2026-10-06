<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    /**
     * @var string
     */
    protected $table = 'short_links';

    /**
     * @var string
     */
    protected $primaryKey = 'short_link_id';

    protected $fillable = ['code', 'target_url'];
}
