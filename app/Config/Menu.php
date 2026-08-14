<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Menu extends BaseConfig
{
    /**
     * Itens do menu. Campo `roles` limita a quem enxerga o item.
     * Sem `roles` = todos autenticados.
     *
     * @var list<array{label: string, items: list<array{page: string, label: string, icon: string, url: string, roles?: list<string>, badge?: int|string}>}>
     */
    public array $items = [
        [
            'label' => 'Principal',
            'items' => [
                ['page' => 'dashboard', 'label' => 'Painel', 'icon' => 'i-dash', 'url' => 'dashboard'],
            ],
        ],
        [
            'label' => 'Cliente',
            'roles' => ['cliente', 'admin'],
            'items' => [
                ['page' => 'buscar', 'label' => 'Buscar prestadores', 'icon' => 'i-search', 'url' => 'prestadores', 'roles' => ['cliente', 'admin']],
                ['page' => 'agendar', 'label' => 'Novo agendamento', 'icon' => 'i-plus', 'url' => 'agendamentos/novo', 'roles' => ['cliente', 'admin']],
                ['page' => 'meus-agendamentos', 'label' => 'Meus agendamentos', 'icon' => 'i-doc', 'url' => 'agendamentos', 'roles' => ['cliente', 'admin']],
                ['page' => 'enderecos', 'label' => 'Meus endereços', 'icon' => 'i-map', 'url' => 'enderecos', 'roles' => ['cliente', 'admin']],
            ],
        ],
        [
            'label' => 'Prestador',
            'roles' => ['prestador', 'admin'],
            'items' => [
                ['page' => 'solicitacoes', 'label' => 'Solicitações', 'icon' => 'i-bell', 'url' => 'prestador/solicitacoes', 'roles' => ['prestador', 'admin']],
                ['page' => 'meus-servicos', 'label' => 'Meus serviços', 'icon' => 'i-doc', 'url' => 'prestador/servicos', 'roles' => ['prestador', 'admin']],
                ['page' => 'perfil-prestador', 'label' => 'Meu perfil', 'icon' => 'i-users', 'url' => 'prestador/perfil', 'roles' => ['prestador', 'admin']],
                ['page' => 'ganhos', 'label' => 'Ganhos', 'icon' => 'i-money', 'url' => 'prestador/ganhos', 'roles' => ['prestador', 'admin']],
            ],
        ],
        [
            'label' => 'Admin',
            'roles' => ['admin'],
            'items' => [
                ['page' => 'admin-usuarios', 'label' => 'Usuários', 'icon' => 'i-users', 'url' => 'admin/usuarios', 'roles' => ['admin']],
                ['page' => 'admin-pagamentos', 'label' => 'Pagamentos & repasses', 'icon' => 'i-money', 'url' => 'admin/pagamentos', 'roles' => ['admin']],
                ['page' => 'admin-comissao', 'label' => 'Comissões MP', 'icon' => 'i-cog', 'url' => 'admin/comissoes', 'roles' => ['admin']],
            ],
        ],
        [
            'label' => 'Conta',
            'items' => [
                ['page' => 'logout', 'label' => 'Sair', 'icon' => 'i-logout', 'url' => 'logout'],
            ],
        ],
    ];
}
