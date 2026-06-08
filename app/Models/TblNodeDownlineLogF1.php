<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblNodeDownlineLogF1 extends Model
{
    use HasFactory;

    protected $table = 'tbl_node_downline_log_f1';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'FUserID',
        'IndirectID',
        'DateCreate',
    ];

    protected $casts = [
        'DateCreate' => 'datetime',
    ];
}