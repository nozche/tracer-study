<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login(): ResponseInterface|string
    {
        if (session()->has('user')) {
            return redirect()->to('/')->with('info', 'Anda sudah masuk.');
        }

        return view('auth/login', [
            'title' => 'Login',
        ]);
    }

    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($this->request->getPost('email'));

        if (! $user || ! password_verify((string) $this->request->getPost('password'), (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Kredensial tidak valid.');
        }

        if ($user['status'] !== 'active') {
            return redirect()->back()->with('error', 'Akun tidak aktif.');
        }

        $roles = $this->getUserRoles((int) $user['id']);

        session()->set('user', [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'roles' => $roles,
        ]);

        return redirect()->to('/')->with('success', 'Login berhasil.');
    }

    public function logout(): RedirectResponse
    {
        session()->remove('user');
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    /**
     * @return list<string>
     */
    private function getUserRoles(int $userId): array
    {
        $builder = db_connect()->table('user_roles');
        $rows = $builder
            ->select('roles.slug')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_column($rows, 'slug')));
    }
}
