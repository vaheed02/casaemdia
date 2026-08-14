<?php

namespace App\Controllers;

use App\Models\PerfilPrestadorModel;

class CatalogoController extends BaseController
{
    public function prestadores(): string
    {
        $this->exigeRole('cliente', 'admin');

        $tipo   = (string) ($this->request->getGet('tipo') ?? '');
        $cidade = trim((string) ($this->request->getGet('cidade') ?? ''));

        return $this->renderPage('pages/catalogo/prestadores', [
            'title'       => 'Buscar prestadores',
            'activePage'  => 'buscar',
            'prestadores' => model(PerfilPrestadorModel::class)->listarDisponiveis($tipo ?: null, $cidade ?: null),
            'filtroTipo'  => $tipo,
            'filtroCidade'=> $cidade,
        ]);
    }
}
