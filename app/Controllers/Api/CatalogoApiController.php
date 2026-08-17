<?php

namespace App\Controllers\Api;

use App\Models\PerfilPrestadorModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Servicos;

class CatalogoApiController extends BaseApiController
{
    public function tipos(): ResponseInterface
    {
        $cfg = config(Servicos::class);

        return $this->jsonOk([
            'tipos'    => $cfg->tipos,
            'comissao' => model(\App\Models\ConfiguracaoModel::class)->comissaoPercentual(),
        ]);
    }

    public function prestadores(): ResponseInterface
    {
        $tipo   = (string) ($this->request->getGet('tipo') ?? '');
        $cidade = trim((string) ($this->request->getGet('cidade') ?? ''));

        $lista = model(PerfilPrestadorModel::class)->listarDisponiveis(
            $tipo !== '' ? $tipo : null,
            $cidade !== '' ? $cidade : null
        );

        return $this->jsonOk($lista);
    }
}
