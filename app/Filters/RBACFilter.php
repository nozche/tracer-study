<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RBACFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if (! $session->has('user')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $session->get('user');
        $roles = $user['roles'] ?? [];

        if ($arguments !== null && $arguments !== []) {
            $allowedRoles = array_map(static fn($role) => (string) $role, $arguments);
            $matched = array_intersect($allowedRoles, $roles);

            if ($matched === []) {
                return redirect()->to('/')->with('error', 'Akses ditolak untuk peran Anda.');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Not used
    }
}
