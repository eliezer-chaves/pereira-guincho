<?php
// Este arquivo vai na pasta public_html/
// Ele substitui o index.html original

$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$clean_uri   = strtok($request_uri, '?'); // Remove query string

// Caminho para o Laravel (uma pasta acima de public_html)
$laravel_path = $_SERVER['DOCUMENT_ROOT'] . '/../click-tracker/public/index.php';

/**
 * Função auxiliar para despachar requisições para o Laravel
 */
function runLaravel($laravel_path, $path_info) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PATH_INFO']   = $path_info;

    if (file_exists($laravel_path)) {
        include $laravel_path;
        exit;
    } else {
        http_response_code(500);
        echo 'Laravel não encontrado em ' . htmlspecialchars($laravel_path);
        exit;
    }
}

// 1. Se a URL começar com /api/, executa o Laravel
if (strpos($request_uri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $path_info = str_replace('/api', '', $request_uri);
    runLaravel($laravel_path, $path_info);
}

// 2. Se a URL for /log-viewer, também vai para o Laravel
if (strpos($clean_uri, '/log-viewer') === 0) {
    runLaravel($laravel_path, $clean_uri);
}

// 3. Lista de rotas válidas do Angular
$valid_angular_routes = [
    '/',
    '/obrigado'
    // Adicione aqui outras rotas do seu Angular conforme necessário
];

// 4. Se for uma rota Angular válida, serve o Angular
if (in_array($clean_uri, $valid_angular_routes)) {
    $angular_file = __DIR__ . '/index.html';

    if (file_exists($angular_file)) {
        http_response_code(200);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        echo file_get_contents($angular_file);
    } else {
        http_response_code(500);
        echo '<h1>Erro: Arquivo index.html não encontrado</h1>';
    }
    exit;
}

// 5. Se chegou aqui, é uma rota não encontrada - serve a página 404.html
$page_404 = __DIR__ . '/404.html';

if (file_exists($page_404)) {
    http_response_code(404);
    echo file_get_contents($page_404);
} else {
    // Fallback se não encontrar 404.html
    http_response_code(404);
    echo '<!DOCTYPE html>';
    echo '<html><head><title>404 - Página não encontrada</title></head><body>';
    echo '<h1>404 - Página não encontrada</h1>';
    echo '<p><a href="/">Voltar ao início</a></p>';
    echo '</body></html>';
}
