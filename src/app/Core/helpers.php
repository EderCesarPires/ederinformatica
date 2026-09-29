<?php
/**
 * Funções auxiliares usadas nas views.
 */

use App\Core\Session;

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money(float|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Session::csrfToken()) . '">';
}

function old(array $old, string $key, mixed $default = ''): string
{
    return e($old[$key] ?? $default);
}

function format_document(string $doc): string
{
    $d = preg_replace('/\D/', '', $doc);
    if (strlen($d) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d);
    }
    if (strlen($d) === 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d);
    }
    return $doc;
}

function format_cep(string $cep): string
{
    $d = preg_replace('/\D/', '', $cep);
    return strlen($d) === 8 ? substr($d, 0, 5) . '-' . substr($d, 5) : $cep;
}

function is_active(string $prefix): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return str_starts_with($uri, $prefix) ? 'active' : '';
}
