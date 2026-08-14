<?php

namespace App\Controllers;

/**
 * Landing page pública do marketplace Casa em Dia.
 */
class LandingController extends BaseController
{
    public function index(): string
    {
        $logado = session()->get('id') !== null;

        return view('landing/index', [
            'title'       => 'Serviços sob demanda para casa e exterior',
            'appNome'     => config('App')->appName,
            'appDescricao'=> config('App')->appDescricao,
            'appLogo'     => base_url(config('App')->appLogo),
            'appIcon'     => base_url(config('App')->appIcon),
            'logado'      => $logado,
            'role'        => (string) (session('role') ?? ''),
            'usuarioNome' => (string) (session('nome') ?? ''),
            'comissao'    => config('Servicos')->comissaoPercentual,
            'tipos'       => config('Servicos')->tipos,
        ]);
    }

    /**
     * Atalho "Solicitar serviço":
     * - cliente/admin autenticado → catálogo
     * - prestador → painel
     * - visitante → login com retorno ao catálogo
     */
    public function solicitar(): mixed
    {
        if (! session()->get('id')) {
            return redirect()
                ->to(base_url('login') . '?redirect=prestadores')
                ->with('error', 'Entre ou crie sua conta para solicitar um serviço.');
        }

        $role = (string) session('role');

        if ($role === 'prestador') {
            return redirect()
                ->to(base_url('dashboard'))
                ->with('error', 'Contas de prestador não agendam serviços como cliente. Use o painel de solicitações.');
        }

        return redirect()->to(base_url('prestadores'));
    }

    /**
     * Atalho para área administrativa (sempre exige autenticação de admin).
     */
    public function admin(): mixed
    {
        if (! session()->get('id')) {
            return redirect()
                ->to(base_url('login') . '?redirect=admin/usuarios')
                ->with('error', 'Acesso restrito. Faça login como administrador.');
        }

        if (session('role') !== 'admin') {
            return redirect()
                ->to(base_url('dashboard'))
                ->with('error', 'Você não tem permissão de administrador.');
        }

        return redirect()->to(base_url('admin/usuarios'));
    }
}
