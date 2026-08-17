<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rotas públicas (landing + auth)
$routes->get('/', 'LandingController::index');
$routes->get('solicitar', 'LandingController::solicitar');
$routes->get('admin-acesso', 'LandingController::admin');

$routes->get('login', 'AuthController::index');
$routes->post('login/autenticar', 'AuthController::autenticar');
$routes->get('cadastro', 'AuthController::cadastro');
$routes->post('cadastro', 'AuthController::registrar');
$routes->get('logout', 'AuthController::logout');

// Webhooks públicos (Mercado Pago)
$routes->post('webhooks/mercadopago', 'WebhookController::mercadopago');
$routes->get('webhooks/mercadopago', 'WebhookController::mercadopago');

// Retorno do checkout (cliente autenticado ou não — preferir logado)
$routes->get('pagamentos/retorno', 'PaymentController::retorno');

// =====================================================================
// API mobile v1 (JSON + JWT) — app único cliente + prestador
// =====================================================================
$routes->options('api/v1/(:any)', static function () {
    return service('response')
        ->setHeader('Access-Control-Allow-Origin', '*')
        ->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->setStatusCode(204);
});

$routes->group('api/v1', ['filter' => 'cors'], static function ($routes) {
    $routes->get('health', static function () {
        return service('response')->setJSON([
            'ok'      => true,
            'app'     => 'Casa em Dia API',
            'version' => '1.0',
            'time'    => date('c'),
        ]);
    });

    $routes->post('auth/login', 'Api\AuthApiController::login');
    $routes->post('auth/register', 'Api\AuthApiController::register');
    $routes->get('tipos', 'Api\CatalogoApiController::tipos');

    $routes->group('', ['filter' => 'apiAuth'], static function ($routes) {
        $routes->get('me', 'Api\AuthApiController::me');
        $routes->get('dashboard', 'Api\HomeApiController::dashboard');

        $routes->get('prestadores', 'Api\CatalogoApiController::prestadores');

        $routes->get('enderecos', 'Api\EnderecoApiController::index');
        $routes->post('enderecos', 'Api\EnderecoApiController::create');
        $routes->delete('enderecos/(:num)', 'Api\EnderecoApiController::delete/$1');

        $routes->get('agendamentos', 'Api\AgendamentoApiController::index');
        $routes->post('agendamentos', 'Api\AgendamentoApiController::create');
        $routes->get('agendamentos/(:num)', 'Api\AgendamentoApiController::show/$1');
        $routes->post('agendamentos/(:num)/(:segment)', 'Api\AgendamentoApiController::acao/$1/$2');

        $routes->get('pagamentos/(:num)', 'Api\PagamentoApiController::show/$1');
        $routes->get('pagamentos/(:num)/checkout', 'Api\PagamentoApiController::checkout/$1');
        $routes->post('pagamentos/(:num)/sincronizar', 'Api\PagamentoApiController::sincronizar/$1');

        $routes->get('prestador/solicitacoes', 'Api\PrestadorApiController::solicitacoes');
        $routes->get('prestador/servicos', 'Api\PrestadorApiController::servicos');
        $routes->get('prestador/ganhos', 'Api\PrestadorApiController::ganhos');
        $routes->get('prestador/perfil', 'Api\PrestadorApiController::perfil');
        $routes->put('prestador/perfil', 'Api\PrestadorApiController::salvarPerfil');
        $routes->post('prestador/perfil', 'Api\PrestadorApiController::salvarPerfil');
    });
});


$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('pagamentos/checkout/(:num)', 'PaymentController::checkout/$1');
    $routes->post('pagamentos/mock/(:num)/confirmar', 'PaymentController::confirmarMock/$1');
    $routes->post('pagamentos/(:num)/sincronizar', 'PaymentController::sincronizar/$1');
    $routes->get('dashboard', 'Home::index');

    // Catálogo (cliente)
    $routes->get('prestadores', 'CatalogoController::prestadores');

    // Endereços (cliente)
    $routes->get('enderecos', 'EnderecoController::index');
    $routes->post('enderecos', 'EnderecoController::salvar');
    $routes->post('enderecos/(:num)/excluir', 'EnderecoController::excluir/$1');

    // Agendamentos
    $routes->get('agendamentos', 'AgendamentoController::index');
    $routes->get('agendamentos/novo', 'AgendamentoController::novo');
    $routes->post('agendamentos', 'AgendamentoController::criar');
    $routes->get('agendamentos/(:num)', 'AgendamentoController::show/$1');
    $routes->post('agendamentos/(:num)/confirmar', 'AgendamentoController::confirmar/$1');
    $routes->post('agendamentos/(:num)/cancelar', 'AgendamentoController::cancelar/$1');

    // Prestador
    $routes->get('prestador/solicitacoes', 'PrestadorController::solicitacoes');
    $routes->get('prestador/servicos', 'PrestadorController::servicos');
    $routes->get('prestador/perfil', 'PrestadorController::perfil');
    $routes->post('prestador/perfil', 'PrestadorController::salvarPerfil');
    $routes->get('prestador/ganhos', 'PrestadorController::ganhos');
    $routes->post('prestador/agendamentos/(:num)/aceitar', 'PrestadorController::aceitar/$1');
    $routes->post('prestador/agendamentos/(:num)/rejeitar', 'PrestadorController::rejeitar/$1');
    $routes->post('prestador/agendamentos/(:num)/iniciar', 'PrestadorController::iniciar/$1');
    $routes->post('prestador/agendamentos/(:num)/concluir', 'PrestadorController::concluir/$1');

    // Admin
    $routes->get('admin/usuarios', 'AdminController::usuarios');
    $routes->get('admin/pagamentos', 'AdminController::pagamentos');
    $routes->get('admin/comissoes', 'AdminController::comissoes');
    $routes->post('admin/comissoes', 'AdminController::salvarComissao');
    $routes->post('admin/pagamentos/(:num)/repassar', 'AdminController::repassar/$1');
    $routes->post('admin/pagamentos/(:num)/sincronizar', 'AdminController::sincronizar/$1');
    $routes->post('admin/pagamentos/(:num)/liberar', 'AdminController::liberar/$1');
});
