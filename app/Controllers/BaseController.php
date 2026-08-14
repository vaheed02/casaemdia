<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        helper(['url', 'form']);
    }

    protected function usuarioId(): int
    {
        return (int) session('id');
    }

    protected function usuarioRole(): string
    {
        return (string) (session('role') ?? '');
    }

    protected function exigeRole(string ...$roles): void
    {
        if (! in_array($this->usuarioRole(), $roles, true)) {
            session()->setFlashdata('error', 'Você não tem permissão para acessar esta área.');
            throw new \CodeIgniter\HTTP\Exceptions\RedirectException(base_url('dashboard'));
        }
    }

    protected function menuFiltrado(): array
    {
        $role  = $this->usuarioRole();
        $items = config('Menu')->items;
        $out   = [];

        foreach ($items as $group) {
            if (! empty($group['roles']) && ! in_array($role, $group['roles'], true)) {
                continue;
            }

            $groupItems = [];
            foreach ($group['items'] as $item) {
                if (! empty($item['roles']) && ! in_array($role, $item['roles'], true)) {
                    continue;
                }
                $groupItems[] = $item;
            }

            if ($groupItems === []) {
                continue;
            }

            $group['items'] = $groupItems;
            $out[]          = $group;
        }

        return $out;
    }

    protected function renderPage(string $view, array $data = []): string
    {
        $nome = session('nome') ?? 'Usuário';
        $role = session('role') ?? 'cliente';
        $iniciais = '';

        foreach (preg_split('/\s+/', trim($nome)) as $parte) {
            if ($parte !== '') {
                $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
            }
            if (strlen($iniciais) >= 2) {
                break;
            }
        }

        $data = array_merge([
            'usuarioNome'     => $nome,
            'usuarioEmail'    => session('email') ?? '',
            'usuarioRole'     => $role,
            'usuarioIniciais' => $iniciais !== '' ? $iniciais : 'US',
            'appNome'         => config('App')->appName,
            'appSigla'        => config('App')->appSigla,
            'appDescricao'    => config('App')->appDescricao,
            'appLogo'         => base_url(config('App')->appLogo),
            'appIcon'         => base_url(config('App')->appIcon),
            'menu'            => $this->menuFiltrado(),
            'statusLabels'    => config('Servicos')->statusLabels,
            'statusCores'     => config('Servicos')->statusCores,
            'tiposServico'    => config('Servicos')->tipos,
            'comissaoPct'     => $this->comissaoPercentualAtual(),
        ], $data);

        return view($view, $data);
    }

    protected function flashOk(string $msg): void
    {
        session()->setFlashdata('success', $msg);
    }

    protected function flashErro(string $msg): void
    {
        session()->setFlashdata('error', $msg);
    }

    protected function money(float|string|null $v): string
    {
        return 'R$ ' . number_format((float) $v, 2, ',', '.');
    }

    protected function comissaoPercentualAtual(): float
    {
        try {
            return model(\App\Models\ConfiguracaoModel::class)->comissaoPercentual();
        } catch (\Throwable) {
            return (float) config('Servicos')->comissaoPercentual;
        }
    }
}
