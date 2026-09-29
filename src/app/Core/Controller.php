<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Controller base: utilitários comuns para todos os controllers.
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    /** Bloqueia o acesso de visitantes não autenticados. */
    protected function requireAuth(): array
    {
        $user = Session::user();
        if ($user === null) {
            Session::flash('error', 'Faça login para continuar.');
            $this->redirect('/login');
        }
        return $user;
    }

    /** Valida o token CSRF de requisições POST. */
    protected function verifyCsrf(): void
    {
        if (!Session::validateCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            Session::flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
        }
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }

    /** Volta para o formulário mantendo os dados digitados e exibindo os erros. */
    protected function backWithErrors(string $path, array $errors): never
    {
        foreach ($errors as $error) {
            Session::flash('error', $error);
        }
        Session::setOld($_POST);
        $this->redirect($path);
    }

    protected function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Registro não encontrado'], 'auth');
        exit;
    }
}
