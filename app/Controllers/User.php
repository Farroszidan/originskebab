<?php

namespace App\Controllers;

class User extends BaseController
{
    public function index()
    {
        // Pastikan hanya user yang bisa akses
        if (!in_groups('user')) {
            return redirect()->to('login');
        }
        return view('user/index_user');
    }
}
