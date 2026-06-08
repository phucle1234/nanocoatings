<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Contact extends Model
{
    use CrudTrait;

    protected $table = 'contacts';

    protected $fillable = [
        'Title',
        'Fullname',
        'Phone',
        'Email',
        'Content',
        'Invoice',
        'QRcode',
        'Type',
        'Date',
        'user_id',
        'order_number',
        'Status',
    ];
}
