<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Renderiza uma view dentro de um layout.
 */
final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);
        $flash = Session::pullFlash();
        $old = Session::pullOld();
        $currentUser = Session::user();

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
    }
}
