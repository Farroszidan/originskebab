<?php

namespace App\Controllers;

use Myth\Auth\Controllers\AuthController as MythAuthController;
use CodeIgniter\HTTP\ResponseInterface;
use Myth\Auth\Config\Auth as MythAuthConfig;

class Auth extends MythAuthController
{
    public function attemptLogin(): ResponseInterface
    {
        $auth = service('authentication');
        $loginInput = $this->request->getPost('login');
        $authConfig = new MythAuthConfig();
        $validFields = $authConfig->validFields;
        $credentials = [
            'password' => $this->request->getPost('password'),
        ];

        foreach ($validFields as $field) {
            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL) && $field === 'email') {
                $credentials['email'] = $loginInput;
                break;
            } elseif ($field === 'username') {
                $credentials['username'] = $loginInput;
                break;
            }
        }

        if ($auth->attempt($credentials)) {
            return redirect()->to(site_url('dashboard'));
        } else {
            return redirect()->back()->withInput()->with('error', $auth->error() ?? 'Login gagal');
        }
    }


    public function registerForm()
    {
        $outlets = model('OutletModel')->findAll();

        return view('auth/register', [
            'outlets' => $outlets
        ]);
    }

    public function register()
    {
        $users = model('UserModel');

        $rules = [
            'username'     => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'        => 'required|valid_email|is_unique[users.email]',
            'password'     => 'required|strong_password',
            'pass_confirm' => 'required|matches[password]',
            'role'         => 'required|in_list[penjualan,produksi,keuangan]',
        ];

        if ($this->request->getPost('role') === 'penjualan') {
            $rules['outlet_id'] = 'required|is_not_unique[outlet.id]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $outletId = $this->request->getPost('role') === 'penjualan'
            ? $this->request->getPost('outlet_id') : null;

        $user = new \Myth\Auth\Entities\User([
            'username'   => $this->request->getPost('username'),
            'email'      => $this->request->getPost('email'),
            'password'   => $this->request->getPost('password'),
            'active'     => 1,
            'outlet_id'  => $outletId,
        ]);

        if (!$users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        $userId = $users->getInsertID();

        $role = $this->request->getPost('role');
        $group = service('authorization');
        $group->addUserToGroup($userId, $role);

        return redirect()->to('login')->with('message', 'Registrasi berhasil. Silakan login.');
    }
}
