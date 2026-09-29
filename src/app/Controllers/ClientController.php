<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\ClientRepository;

final class ClientController extends Controller
{
    private ClientRepository $clients;

    public function __construct()
    {
        $this->requireAuth();
        $this->clients = new ClientRepository();
    }

    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $this->view('clients/index', [
            'title'   => 'Clientes',
            'q'       => $q,
            'clients' => $q !== '' ? $this->clients->search($q) : $this->clients->all('name ASC'),
        ]);
    }

    public function create(): void
    {
        $this->view('clients/form', ['title' => 'Novo cliente', 'client' => null]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->validated('/clients/create');
        $this->clients->create($data);
        Session::flash('success', 'Cliente cadastrado com sucesso.');
        $this->redirect('/clients');
    }

    public function edit(int $id): void
    {
        $client = $this->clients->find($id) ?? $this->notFound();
        $this->view('clients/form', ['title' => 'Editar cliente', 'client' => $client]);
    }

    public function update(int $id): void
    {
        $this->verifyCsrf();
        $this->clients->find($id) ?? $this->notFound();
        $data = $this->validated("/clients/{$id}/edit", $id);
        $this->clients->update($id, $data);
        Session::flash('success', 'Cliente atualizado com sucesso.');
        $this->redirect('/clients');
    }

    public function destroy(int $id): void
    {
        $this->verifyCsrf();
        $this->clients->find($id) ?? $this->notFound();

        if ($this->clients->hasOrders($id)) {
            Session::flash('error', 'Este cliente possui ordens de serviço e não pode ser excluído.');
        } else {
            $this->clients->delete($id);
            Session::flash('success', 'Cliente excluído.');
        }
        $this->redirect('/clients');
    }

    private function validated(string $backTo, ?int $ignoreId = null): array
    {
        $name = $this->input('name');
        $document = preg_replace('/\D/', '', $this->input('document'));
        $cep = preg_replace('/\D/', '', $this->input('cep'));
        $address = $this->input('address');
        $complement = $this->input('complement');

        $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 150) {
            $errors[] = 'O nome deve ter entre 3 e 150 caracteres.';
        }
        if (!self::isValidCpf($document) && !self::isValidCnpj($document)) {
            $errors[] = 'Informe um CPF ou CNPJ válido.';
        } elseif ($this->clients->documentExists($document, $ignoreId)) {
            $errors[] = 'Já existe um cliente com este documento.';
        }
        if (strlen($cep) !== 8) {
            $errors[] = 'Informe um CEP válido (8 dígitos).';
        }
        if (mb_strlen($complement) > 150) {
            $errors[] = 'O complemento deve ter no máximo 150 caracteres.';
        }
        if ($errors) {
            $this->backWithErrors($backTo, $errors);
        }

        return [
            'name'       => $name,
            'document'   => $document,
            'cep'        => $cep,
            'address'    => mb_substr($address, 0, 255) ?: null,
            'complement' => $complement ?: null,
        ];
    }

    private static function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }
        return true;
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }
        $weights = [[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]];
        foreach ($weights as $n => $w) {
            $sum = 0;
            foreach ($w as $i => $weight) {
                $sum += (int) $cnpj[$i] * $weight;
            }
            $rest = $sum % 11;
            $digit = $rest < 2 ? 0 : 11 - $rest;
            if ((int) $cnpj[12 + $n] !== $digit) {
                return false;
            }
        }
        return true;
    }
}
