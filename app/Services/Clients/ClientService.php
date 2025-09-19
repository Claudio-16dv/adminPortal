<?php

namespace App\Services\Clients;

use App\Models\Clients\Clients;
use App\Models\Clients\Addresses;
use App\Traits\ValidationClientTrait;
use InvalidArgumentException;

class ClientService
{
    use ValidationClientTrait;

    private Clients $clientModel;
    private Addresses $addressModel;

    public function __construct()
    {
        $this->clientModel = new Clients();
        $this->addressModel = new Addresses();
    }

    public function getAllClients(): array
    {
        $clients = $this->clientModel->getAll();

        foreach ($clients as &$client) {
            $birthdate = \DateTime::createFromFormat('Y-m-d', $client['birthdate']);
            if ($birthdate) {
                $client['birthdate'] = $birthdate->format('d/m/Y'); 
            }

            $client['addresses'] = $this->addressModel->getAddressByClientId($client['id']);
        }

        return $clients;
    }

    public function getClientForEdit(int $id): array
    {
        $client = $this->clientModel->findById($id);

        if (!$client) {
            throw new InvalidArgumentException("Cliente não encontrado.");
        }

        $addresses = $this->addressModel->getAddressByClientId($id);

        return array_merge($client, ['addresses' => $addresses]);
    }

    public function deleteClient(int $id): array
    {
        $client = $this->clientModel->findById($id);

        if (!$client) {
            throw new InvalidArgumentException("Cliente não encontrado.");
        }

        $deleted = $this->clientModel->delete($id);

        if (!$deleted) {
            throw new InvalidArgumentException("Não foi possível excluir o cliente.");
        }

        return ['message' => 'Cliente excluído com sucesso.'];
    }

    public function updateClient(int $id, array $data): array
    {
        $clientData = $data;
        $addresses = [];

        if (isset($clientData['addresses']) && is_array($clientData['addresses'])) {
            $validatedAddresses = [];

            foreach ($clientData['addresses'] as $address) {
                $validatedAddresses[] = $this->validateAddress($address);
            }

            $addresses = $validatedAddresses;
            unset($clientData['addresses']);
        }

        $validated = $this->validateUpdateInput($clientData);

        $client = $this->clientModel->findById($id);
        if (!$client) {
            throw new InvalidArgumentException("Cliente não encontrado.");
        }

        $updated = $this->clientModel->update($id, $validated);
        if (!$updated) {
            throw new InvalidArgumentException("Erro ao atualizar cliente.");
        }

        if (!empty($addresses)) {
            $this->addressModel->updateAddresses($id, $addresses);
        }

        return ['message' => 'Cliente atualizado com sucesso.'];
    }

    public function createClient(array $data): array
    {
        $validated = $this->validateInput($data);

        $clientId = $this->clientModel->create($validated);

        if (!$clientId) {
            throw new InvalidArgumentException("Erro ao cadastrar cliente.");
        }

        if (!empty($validated['addresses'])) {
            $this->addressModel->addAddresses($clientId, $validated['addresses']);
        }

        return [
            'message' => 'Cliente criado com sucesso.',
            'id' => $clientId
        ];
    }
}
