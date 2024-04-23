<?php

namespace Ouhaohan8023\IDInfo\Model;

use Illuminate\Database\Eloquent\Model;

class Xzqh extends Model
{
    protected $table = '202201xzqh';

    protected $fillable = [
        'code',
        'title',
        'type',
        'parent_id',
        'superior_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Xzqh::class, 'parent_id');
    }

    public function superior()
    {
        return $this->belongsTo(Xzqh::class, 'superior_id');
    }
}
