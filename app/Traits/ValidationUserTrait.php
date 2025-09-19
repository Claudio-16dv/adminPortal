<?php

namespace App\Traits;

use InvalidArgumentException;

trait ValidationUserTrait
{
    protected array $allowedFields = ['name', 'email', 'password'];

    public function validateInput(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $this->allowedFields)) {
                throw new InvalidArgumentException("Campo não permitido: {$key}");
            }
        }

        $name     = $this->validateName($data['name'] ?? '');
        $email    = $this->validateEmail($data['email'] ?? '');
        $password = $this->validatePassword($data['password'] ?? '');

        return compact('name', 'email', 'password');
    }

    protected function validateName(string $name): string
    {
        $name = trim($name);

        if (strlen($name) < 3 || strlen($name) > 100) {
            throw new InvalidArgumentException('O nome deve ter entre 3 e 100 caracteres.');
        }

        if (preg_match('/\d/', $name)) {
            throw new InvalidArgumentException('O nome não pode conter números.');
        }

        return $name;
    }

    protected function validateEmail(string $email): string
    {
        $email = trim($email);

        if (empty($email)) {
            throw new InvalidArgumentException('O campo "email" é obrigatório.');
        }

        if (strlen($email) > 100) {
            throw new InvalidArgumentException('O e-mail deve ter no máximo 100 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O e-mail informado é inválido.');
        }

        return $email;
    }

    protected function validatePassword(string $password): string
    {
        if (empty($password)) {
            throw new InvalidArgumentException('O campo "password" é obrigatório.');
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }

        if (strlen($password) > 255) {
            throw new InvalidArgumentException('A senha é muito longa.');
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[\W_]/', $password)) {
            throw new InvalidArgumentException('A senha deve conter pelo menos uma letra maiúscula e um caractere especial.');
        }

        return $password;
    }
}
