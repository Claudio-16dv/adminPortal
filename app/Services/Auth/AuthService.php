<?php

namespace App\Services\Auth;

use App\Models\Users\Users;
use App\Traits\ValidationUserTrait;
use App\Core\JWT;
use InvalidArgumentException;

class AuthService
{
    use ValidationUserTrait;

    private Users $userModel;

    public function __construct()
    {
        $this->userModel = new Users();
    }

    public function createUser(array $data): array
    {
        $validated = $this->validateInput($data);

        if ($this->userModel->emailExists($validated['email'])) {
            throw new InvalidArgumentException('O e-mail informado já está cadastrado.');
        }

        $validated['password'] = password_hash($validated['password'], PASSWORD_DEFAULT);

        $id = $this->userModel->createUser($validated);

        return [
            'message' => 'Usuário criado com sucesso',
            'id' => $id
        ];
    }

    public function login(array $data): array
    {
        $email = $this->validateEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($password)) {
            throw new InvalidArgumentException('A senha é obrigatória.');
        }

        $user = $this->userModel->loginUser($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new InvalidArgumentException('Credenciais inválidas.');
        }

        $token = JWT::generate([
            'sub' => $user['id'],
            'email' => $user['email']
        ]);

        setcookie('token', $token, [
            'expires' => time() + 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Strict'
        ]);

        return [
            'success' => true,
            'message' => 'Login efetuado com sucesso.'
        ];
    }

    public function logout(): array
    {
        setcookie('token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Strict'
        ]);

        return [
            'success' => true,
            'message' => 'Logout efetuado com sucesso.'
        ];
    }
}
