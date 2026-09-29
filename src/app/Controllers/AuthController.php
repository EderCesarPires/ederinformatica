<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UserRepository;

final class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function loginForm(): void
    {
        if (Session::user()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', ['title' => 'Entrar'], 'auth');
    }

    public function login(): void
    {
        $this->verifyCsrf();
        $email = mb_strtolower($this->input('email'));
        $password = (string) ($_POST['password'] ?? '');

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->backWithErrors('/login', ['E-mail ou senha inválidos.']);
        }

        // Atualiza o hash se o custo do bcrypt mudar no futuro
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
            $this->users->update((int) $user['id'], ['password' => password_hash($password, PASSWORD_BCRYPT)]);
        }

        Session::login($user);
        Session::flash('success', 'Bem-vindo(a), ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        if (Session::user()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/register', ['title' => 'Criar conta'], 'auth');
    }

    public function register(): void
    {
        $this->verifyCsrf();
        $name = $this->input('name');
        $email = mb_strtolower($this->input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];
        if (mb_strlen($name) < 3) {
            $errors[] = 'Informe seu nome (mínimo 3 caracteres).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } elseif ($this->users->findByEmail($email)) {
            $errors[] = 'Este e-mail já está cadastrado.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'A senha deve ter no mínimo 6 caracteres.';
        }
        if ($password !== $confirm) {
            $errors[] = 'As senhas não conferem.';
        }
        if ($errors) {
            unset($_POST['password'], $_POST['password_confirmation']);
            $this->backWithErrors('/register', $errors);
        }

        $id = $this->users->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT), // hash seguro bcrypt
        ]);

        Session::login(['id' => $id, 'name' => $name, 'email' => $email]);
        Session::flash('success', 'Conta criada com sucesso!');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Session::logout();
        Session::start();
        Session::flash('success', 'Você saiu do sistema.');
        $this->redirect('/login');
    }
}
