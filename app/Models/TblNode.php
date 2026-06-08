<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblNode extends Model
{
    use HasFactory;

    protected $table = 'tbl_node';
    protected $primaryKey = 'NodeID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'DateCreate',
    ];

    protected $casts = [
        'DateCreate' => 'datetime',
    ];
}