<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\TaskRepository;

final class TaskController extends Controller
{
    private TaskRepository $tasks;

    public function __construct()
    {
        $this->tasks = new TaskRepository();
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $title = $this->input('title');
        if ($title === '' || mb_strlen($title) > 200) {
            Session::flash('error', 'Informe uma tarefa (até 200 caracteres).');
        } else {
            $this->tasks->create(['user_id' => $user['id'], 'title' => $title, 'done' => 0]);
        }
        $this->redirect('/dashboard#tarefas');
    }

    public function toggle(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $task = $this->tasks->findForUser($id, $user['id']) ?? $this->notFound();
        $this->tasks->update($id, ['done' => $task['done'] ? 0 : 1]);
        $this->redirect('/dashboard#tarefas');
    }

    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $this->tasks->findForUser($id, $user['id']) ?? $this->notFound();
        $this->tasks->delete($id);
        $this->redirect('/dashboard#tarefas');
    }
}
