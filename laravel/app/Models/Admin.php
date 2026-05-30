<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['full_name', 'username', 'is_approved', 'phone_number', 'email', 'password', 'password_plaintext'])]
#[Hidden(['password', 'password_plaintext', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]

class Admin extends Authenticatable
{
    //
}
