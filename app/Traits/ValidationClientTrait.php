<?php

namespace App\Traits;

use InvalidArgumentException;

trait ValidationClientTrait
{
    public function validateInput(array $data): array
    {
        $validated = [];

        $validated['name'] = $this->validateName($data['name'] ?? '');
        $validated['cpf'] = $this->validateCpf($data['cpf'] ?? '');
        $validated['rg'] = $this->validateRg($data['rg'] ?? '');
        $validated['birthdate'] = $this->validateBirthdate($data['birthdate'] ?? '');
        $validated['phone'] = $this->validatePhone($data['phone'] ?? '');

        if (empty($data['addresses']) || !is_array($data['addresses'])) {
            throw new InvalidArgumentException('Pelo menos um endereço deve ser informado.');
        }

        $validated['addresses'] = [];

        foreach ($data['addresses'] as $address) {
            $validated['addresses'][] = $this->validateAddress($address);
        }

        return $validated;
    }

    public function validateUpdateInput(array $data): array
    {
        $validated = [];

        if (isset($data['name'])) {
            $validated['name'] = $this->validateName($data['name']);
        }

        if (isset($data['cpf'])) {
            $validated['cpf'] = $this->validateCpf($data['cpf']);
        }

        if (isset($data['rg'])) {
            $validated['rg'] = $this->validateRg($data['rg']);
        }

        if (isset($data['birthdate'])) {
            $validated['birthdate'] = $this->validateBirthdate($data['birthdate']);
        }

        if (isset($data['phone'])) {
            $validated['phone'] = $this->validatePhone($data['phone']);
        }

        if (isset($data['addresses']) && is_array($data['addresses'])) {
            $validated['addresses'] = [];

            foreach ($data['addresses'] as $address) {
                $validated['addresses'][] = $this->validateAddress($address);
            }
        }

        return $validated;
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

    protected function validateCpf(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            throw new InvalidArgumentException('CPF inválido.');
        }

        if (preg_match('/[a-zA-Z]/', $cpf)) {
            throw new InvalidArgumentException('CPF não pode conter letras.');
        }

        return $cpf;
    }

    protected function validateRg(string $rg): string
    {
        $rg = trim($rg);
        if (strlen($rg) < 5 || strlen($rg) > 20) {
            throw new InvalidArgumentException('RG deve ter entre 5 e 20 caracteres.');
        }
        return $rg;
    }

    protected function validateBirthdate(string $birthdate): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
            $date = \DateTime::createFromFormat('Y-m-d', $birthdate);
            if (!$date) {
                throw new InvalidArgumentException('Data de nascimento inválida.');
            }
            return $date->format('Y-m-d');
        }

        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $birthdate)) {
            $date = \DateTime::createFromFormat('Y/m/d', $birthdate);
            if (!$date) {
                throw new InvalidArgumentException('Data de nascimento inválida.');
            }
            return $date->format('Y-m-d');
        }

        $birthdate = str_replace('-', '/', $birthdate);

        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birthdate)) {
            throw new InvalidArgumentException('Data de nascimento inválida. Use os formatos dd/mm/yyyy, dd-mm-yyyy, ou yyyy-mm-dd.');
        }

        $date = \DateTime::createFromFormat('d/m/Y', $birthdate);
        if (!$date) {
            throw new InvalidArgumentException('Data de nascimento inválida.');
        }

        return $date->format('Y-m-d');
    }

    protected function validatePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10 || strlen($phone) > 20) {
            throw new InvalidArgumentException('Telefone deve ter entre 10 e 20 dígitos.');
        }

        if (preg_match('/[a-zA-Z]/', $phone)) {
            throw new InvalidArgumentException('Telefone não pode conter letras.');
        }
        
        return $phone;
    }

    protected function validateAddress(array $address): array
    {
        $required = ['street', 'number', 'neighborhood', 'city', 'state', 'zip_code'];
        foreach ($required as $field) {
            if (empty($address[$field])) {
                throw new InvalidArgumentException("O campo '$field' é obrigatório no endereço.");
            }
        }

        if (strlen($address['street']) > 100) {
            throw new InvalidArgumentException('Rua deve ter no máximo 100 caracteres.');
        }

        if (strlen($address['number']) > 10) {
            throw new InvalidArgumentException('Número deve ter no máximo 10 caracteres.');
        }

        if (strlen($address['neighborhood']) > 100) {
            throw new InvalidArgumentException('Bairro deve ter no máximo 100 caracteres.');
        }

        if (strlen($address['city']) > 100) {
            throw new InvalidArgumentException('Cidade deve ter no máximo 100 caracteres.');
        }

        if (strlen($address['state']) !== 2) {
            throw new InvalidArgumentException('UF deve conter exatamente 2 caracteres.');
        }

        if (strlen($address['zip_code']) > 10) {
            throw new InvalidArgumentException('CEP deve ter no máximo 10 caracteres.');
        }

        if (!empty($address['complement']) && strlen($address['complement']) > 100) {
            throw new InvalidArgumentException('Complemento deve ter no máximo 100 caracteres.');
        }

        return [
            'id' => $address['id'] ?? null,
            'street' => trim($address['street']),
            'number' => trim($address['number']),
            'neighborhood' => trim($address['neighborhood']),
            'city' => trim($address['city']),
            'state' => strtoupper(trim($address['state'])),
            'zip_code' => preg_replace('/[^0-9\-]/', '', $address['zip_code']),
            'complement' => $address['complement'] ?? ''
        ];
    }
}
