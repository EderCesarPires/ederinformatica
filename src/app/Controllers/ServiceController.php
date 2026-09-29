<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\ServiceRepository;

final class ServiceController extends Controller
{
    private ServiceRepository $services;

    public function __construct()
    {
        $this->requireAuth();
        $this->services = new ServiceRepository();
    }

    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $this->view('services/index', [
            'title'    => 'Serviços',
            'q'        => $q,
            'services' => $q !== '' ? $this->services->search($q) : $this->services->all('name ASC'),
        ]);
    }

    public function create(): void
    {
        $this->view('services/form', ['title' => 'Novo serviço', 'service' => null]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->validated('/services/create');
        $this->services->create($data);
        Session::flash('success', 'Serviço cadastrado com sucesso.');
        $this->redirect('/services');
    }

    public function edit(int $id): void
    {
        $service = $this->services->find($id) ?? $this->notFound();
        $this->view('services/form', ['title' => 'Editar serviço', 'service' => $service]);
    }

    public function update(int $id): void
    {
        $this->verifyCsrf();
        $this->services->find($id) ?? $this->notFound();
        $data = $this->validated("/services/{$id}/edit");
        $this->services->update($id, $data);
        Session::flash('success', 'Serviço atualizado com sucesso.');
        $this->redirect('/services');
    }

    public function destroy(int $id): void
    {
        $this->verifyCsrf();
        $this->services->find($id) ?? $this->notFound();

        if ($this->services->isInUse($id)) {
            Session::flash('error', 'Este serviço está vinculado a ordens de serviço e não pode ser excluído.');
        } else {
            $this->services->delete($id);
            Session::flash('success', 'Serviço excluído.');
        }
        $this->redirect('/services');
    }

    private function validated(string $backTo): array
    {
        $name = $this->input('name');
        $description = $this->input('description');
        $price = $this->input('price');
        if (str_contains($price, ',')) { // formato brasileiro: 1.234,56
            $price = str_replace(',', '.', str_replace('.', '', $price));
        }

        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors[] = 'O nome do serviço deve ter entre 2 e 150 caracteres.';
        }
        if (mb_strlen($description) > 255) {
            $errors[] = 'A descrição deve ter no máximo 255 caracteres.';
        }
        if (!is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Informe um valor válido.';
        }
        if ($errors) {
            $this->backWithErrors($backTo, $errors);
        }

        return ['name' => $name, 'description' => $description ?: null, 'price' => round((float) $price, 2)];
    }
}
