<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $fillable = [
        'chat_id',
        'user_name',
        'first_name',
        'phone',
        'language_code',
        'path_image_qr_code',
        'verification',
        'path_image_verification'
    ];

}
