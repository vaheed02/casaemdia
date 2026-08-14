<?php

namespace App\Controllers;

use App\Models\PerfilPrestadorModel;
use App\Models\UsuarioModel;

class AuthController extends BaseController
{
    public function index(): mixed
    {
        if (session()->has('id')) {
            return redirect()->to($this->destinoPosLogin((string) session('role')));
        }

        return view('auth/login', [
            'title'    => 'Entrar',
            'appNome'  => config('App')->appName,
            'appLogo'  => base_url(config('App')->appLogo),
            'appIcon'  => base_url(config('App')->appIcon),
            'redirect' => $this->redirectSeguro($this->request->getGet('redirect')),
        ]);
    }

    public function cadastro(): mixed
    {
        if (session()->has('id')) {
            return redirect()->to($this->destinoPosLogin((string) session('role')));
        }

        return view('auth/cadastro', [
            'title'   => 'Criar conta',
            'appNome' => config('App')->appName,
            'appLogo' => base_url(config('App')->appLogo),
            'appIcon' => base_url(config('App')->appIcon),
            'rolePref'=> in_array($this->request->getGet('role'), ['cliente', 'prestador'], true)
                ? $this->request->getGet('role')
                : 'cliente',
        ]);
    }

    public function registrar(): mixed
    {
        try {
            $rules = [
                'nome'  => 'required|min_length[3]|max_length[120]',
                'email' => 'required|valid_email|is_unique[usuarios.email]',
                'senha' => 'required|min_length[6]',
                'role'  => 'required|in_list[cliente,prestador]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $role = $this->request->getPost('role');
            $id   = model(UsuarioModel::class)->insert([
                'nome'     => trim((string) $this->request->getPost('nome')),
                'email'    => strtolower(trim((string) $this->request->getPost('email'))),
                'senha'    => password_hash((string) $this->request->getPost('senha'), PASSWORD_DEFAULT),
                'telefone' => trim((string) $this->request->getPost('telefone')),
                'role'     => $role,
                'ativo'    => 1,
            ]);

            if ($role === 'prestador') {
                model(PerfilPrestadorModel::class)->insert([
                    'usuario_id'       => $id,
                    'tipos_servico'    => 'diarista',
                    'bio'              => '',
                    'valor_diaria'     => 150,
                    'valor_passeio'    => 40,
                    'valor_telhado'    => 0,
                    'valor_piscina'    => 0,
                    'valor_jardim'     => 0,
                    'valor_hidraulico' => 0,
                    'cidade'           => '',
                    'bairro'           => '',
                    'disponivel'       => 1,
                ]);
            }

            $usuario = model(UsuarioModel::class)->find($id);
            $this->montarSessao($usuario);

            $destino = $role === 'cliente' ? 'prestadores' : 'dashboard';

            return redirect()->to(base_url($destino))->with('success', 'Conta criada com sucesso!');
        } catch (\Throwable $e) {
            log_message('error', 'Cadastro falhou: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->withInput()->with(
                'error',
                'Não foi possível concluir o cadastro. Verifique a conexão com o banco (usuario/senha no .env) e se o schema.sql foi importado. Detalhe: ' . $e->getMessage()
            );
        }
    }

    public function autenticar(): mixed
    {
        try {
            $email = strtolower(trim((string) $this->request->getPost('email')));
            $senha = (string) $this->request->getPost('senha');
            $usuario = model(UsuarioModel::class)->findAtivoByEmail($email);

            if ($usuario === null || ! password_verify($senha, $usuario['senha'])) {
                return redirect()->back()->withInput()->with('error', 'E-mail ou senha inválidos.');
            }

            $this->montarSessao($usuario);

            $redirect = $this->redirectSeguro($this->request->getPost('redirect'));
            if ($redirect !== null) {
                if (str_starts_with($redirect, 'admin') && $usuario['role'] !== 'admin') {
                    return redirect()
                        ->to(base_url('dashboard'))
                        ->with('error', 'Você não tem permissão de administrador.');
                }

                return redirect()->to(base_url($redirect));
            }

            return redirect()->to($this->destinoPosLogin((string) $usuario['role']));
        } catch (\Throwable $e) {
            log_message('error', 'Login falhou: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->withInput()->with(
                'error',
                'Falha ao entrar. Confira o .env (MySQL) e se as tabelas existem. Detalhe: ' . $e->getMessage()
            );
        }
    }

    private function montarSessao(array $usuario): void
    {
        session()->set([
            'id'    => $usuario['id'],
            'nome'  => $usuario['nome'],
            'email' => $usuario['email'],
            'role'  => $usuario['role'],
        ]);
    }

    public function logout(): mixed
    {
        session()->destroy();

        return redirect()->to(base_url('/'))->with('success', 'Você saiu da conta.');
    }

    /**
     * Destino padrão após login conforme o perfil.
     */
    private function destinoPosLogin(string $role): string
    {
        return match ($role) {
            'admin'     => base_url('admin/usuarios'),
            'prestador' => base_url('prestador/solicitacoes'),
            default     => base_url('dashboard'),
        };
    }

    /**
     * Aceita apenas paths internos relativos (sem URL absoluta / scheme).
     */
    private function redirectSeguro(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '://') || str_starts_with($path, '//')) {
            return null;
        }

        // lista branca simples de destinos usados pela landing
        $allowedPrefixes = [
            'dashboard',
            'prestadores',
            'agendamentos',
            'enderecos',
            'prestador',
            'admin',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $path;
            }
        }

        return null;
    }
}
