<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entries';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_publish' => 'bool',
    ];

    /**
     * 更新日時取得
     *
     * @return string 更新日時(Y-m-d H:i:s形式)
     */
    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 親記事取得
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_entry_id');
    }
}
