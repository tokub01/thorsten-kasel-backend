<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingContactRequest extends Model
{
    protected $table = 'pending_contact_requests';

    protected $fillable = [
        'email',
        'name',
        'message',
        'token',
        'isVerified'
      ];
}
