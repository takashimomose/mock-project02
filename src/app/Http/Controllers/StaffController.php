<?php

namespace App\Http\Controllers;

use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $staffMembers = User::where('role_id', 2)->get(['id', 'name', 'email']);

        return view('admin.staff-list', compact('staffMembers'));
    }

    public function detail()
    {
        return view('admin.staff-detail');
    }
}
