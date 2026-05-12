<?php

namespace App\Http\Controllers\Api\Profiles;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CreatorDropsController extends Controller
{
    public function __invoke(Request $request, User $user) {}
}
