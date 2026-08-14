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
