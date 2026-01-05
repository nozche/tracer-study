<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FakultasModel;
use App\Models\ProfileModel;
use App\Models\ProgramStudiModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Users extends BaseController
{
    private UserModel $userModel;
    private RoleModel $roleModel;
    private ProfileModel $profileModel;
    private FakultasModel $fakultasModel;
    private ProgramStudiModel $programStudiModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->profileModel = new ProfileModel();
        $this->fakultasModel = new FakultasModel();
        $this->programStudiModel = new ProgramStudiModel();
    }

    public function index(): string
    {
        $users = $this->userModel
            ->withRelations()
            ->orderBy('users.id', 'DESC')
            ->findAll();

        return view('admin/users/index', [
            'title' => 'Kelola Pengguna',
            'users' => $users,
        ]);
    }

    public function create(): string
    {
        return view('admin/users/create', [
            'title'          => 'Tambah Pengguna',
            'roles'          => $this->roleModel->findAll(),
            'fakultas'       => $this->fakultasModel->findAll(),
            'programStudi'   => $this->programStudiModel->findAll(),
            'validation'     => session('errors') ?? [],
        ]);
    }

    public function store(): RedirectResponse
    {
        $rules = [
            'name'             => 'required|min_length[3]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'status'           => 'required|in_list[active,inactive]',
            'role_id'          => 'required|is_not_unique[roles.id]',
            'fakultas_id'      => 'permit_empty|is_not_unique[fakultas.id]',
            'program_studi_id' => 'permit_empty|is_not_unique[program_studi.id]',
            'phone'            => 'permit_empty|min_length[8]',
            'address'          => 'permit_empty|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->userModel->insert([
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'        => $this->request->getPost('status'),
        ]);

        $this->syncRole((int) $userId, (int) $this->request->getPost('role_id'));

        $this->profileModel->insert([
            'user_id'          => $userId,
            'fakultas_id'      => $this->request->getPost('fakultas_id') ?: null,
            'program_studi_id' => $this->request->getPost('program_studi_id') ?: null,
            'phone'            => $this->request->getPost('phone'),
            'address'          => $this->request->getPost('address'),
        ]);

        return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(int $id): string
    {
        $user = $this->userModel->findWithRelations($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $roleId = db_connect()->table('user_roles')->where('user_id', $id)->get()->getRow()?->role_id;

        return view('admin/users/edit', [
            'title'          => 'Ubah Pengguna',
            'user'           => $user,
            'roles'          => $this->roleModel->findAll(),
            'currentRoleId'  => $roleId,
            'fakultas'       => $this->fakultasModel->findAll(),
            'programStudi'   => $this->programStudiModel->findAll(),
            'validation'     => session('errors') ?? [],
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $rules = [
            'name'             => 'required|min_length[3]',
            'email'            => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'password'         => 'permit_empty|min_length[8]',
            'status'           => 'required|in_list[active,inactive]',
            'role_id'          => 'required|is_not_unique[roles.id]',
            'fakultas_id'      => 'permit_empty|is_not_unique[fakultas.id]',
            'program_studi_id' => 'permit_empty|is_not_unique[program_studi.id]',
            'phone'            => 'permit_empty|min_length[8]',
            'address'          => 'permit_empty|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'   => $this->request->getPost('name'),
            'email'  => $this->request->getPost('email'),
            'status' => $this->request->getPost('status'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $updateData);
        $this->syncRole($id, (int) $this->request->getPost('role_id'));

        $profileData = [
            'fakultas_id'      => $this->request->getPost('fakultas_id') ?: null,
            'program_studi_id' => $this->request->getPost('program_studi_id') ?: null,
            'phone'            => $this->request->getPost('phone'),
            'address'          => $this->request->getPost('address'),
        ];

        $existingProfile = $this->profileModel->where('user_id', $id)->first();
        if ($existingProfile) {
            $this->profileModel->update((int) $existingProfile['id'], $profileData);
        } else {
            $profileData['user_id'] = $id;
            $this->profileModel->insert($profileData);
        }

        return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        $currentUser = session('user')['id'] ?? null;
        if ($currentUser !== null && (int) $currentUser === $id) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        db_connect()->table('user_roles')->where('user_id', $id)->delete();
        $this->profileModel->where('user_id', $id)->delete();
        $this->userModel->delete($id);

        return redirect()->to('/admin/users')->with('success', 'Pengguna telah dihapus.');
    }

    private function syncRole(int $userId, int $roleId): void
    {
        $builder = db_connect()->table('user_roles');
        $exists = $builder->where(['user_id' => $userId, 'role_id' => $roleId])->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $builder->where('user_id', $userId)->delete();
        $builder->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }
}
