<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now  = date('Y-m-d H:i:s');
        $hash = password_hash('demo123', PASSWORD_DEFAULT);

        $this->db->table('usuarios')->insertBatch([
            [
                'nome'       => 'Admin Casa em Dia',
                'email'      => 'admin@demo.com',
                'senha'      => $hash,
                'telefone'   => '(11) 90000-0001',
                'role'       => 'admin',
                'ativo'      => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome'       => 'Ana Cliente',
                'email'      => 'cliente@demo.com',
                'senha'      => $hash,
                'telefone'   => '(11) 91111-1111',
                'role'       => 'cliente',
                'ativo'      => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome'       => 'Bruno Diarista',
                'email'      => 'prestador@demo.com',
                'senha'      => $hash,
                'telefone'   => '(11) 92222-2222',
                'role'       => 'prestador',
                'ativo'      => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome'       => 'Carla Passeadora',
                'email'      => 'passeadora@demo.com',
                'senha'      => $hash,
                'telefone'   => '(11) 93333-3333',
                'role'       => 'prestador',
                'ativo'      => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $adminId      = (int) $this->db->table('usuarios')->where('email', 'admin@demo.com')->get()->getRow('id');
        $clienteId    = (int) $this->db->table('usuarios')->where('email', 'cliente@demo.com')->get()->getRow('id');
        $diaristaId   = (int) $this->db->table('usuarios')->where('email', 'prestador@demo.com')->get()->getRow('id');
        $passeadoraId = (int) $this->db->table('usuarios')->where('email', 'passeadora@demo.com')->get()->getRow('id');

        $this->db->table('perfis_prestador')->insertBatch([
            [
                'usuario_id'       => $diaristaId,
                'tipos_servico'    => 'diarista,jardins',
                'bio'              => 'Diarista e jardinagem leve. 8 anos em apartamentos e casas.',
                'valor_diaria'     => 180.00,
                'valor_passeio'    => 0,
                'valor_telhado'    => 0,
                'valor_piscina'    => 0,
                'valor_jardim'     => 120.00,
                'valor_hidraulico' => 0,
                'cidade'           => 'São Paulo',
                'bairro'           => 'Vila Mariana',
                'avaliacao_media'  => 4.80,
                'total_avaliacoes' => 12,
                'disponivel'       => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'usuario_id'       => $passeadoraId,
                'tipos_servico'    => 'passeador,diarista,piscinas',
                'bio'              => 'Pets, diárias leves e manutenção básica de piscina.',
                'valor_diaria'     => 160.00,
                'valor_passeio'    => 45.00,
                'valor_telhado'    => 0,
                'valor_piscina'    => 150.00,
                'valor_jardim'     => 0,
                'valor_hidraulico' => 0,
                'cidade'           => 'São Paulo',
                'bairro'           => 'Pinheiros',
                'avaliacao_media'  => 4.90,
                'total_avaliacoes' => 20,
                'disponivel'       => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // Prestador multi-nicho (telhado + hidráulica) — reutiliza admin só se quiser; cria terceiro prestador via insert se necessário
        // Mantém seed enxuto: os dois perfis acima cobrem diarista, jardins, passeador e piscinas.


        $this->db->table('enderecos')->insert([
            'usuario_id'  => $clienteId,
            'titulo'      => 'Casa',
            'cep'         => '04101-000',
            'logradouro'  => 'Rua Domingos de Morais',
            'numero'      => '1000',
            'complemento' => 'Apto 42',
            'bairro'      => 'Vila Mariana',
            'cidade'      => 'São Paulo',
            'uf'          => 'SP',
            'principal'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // Mantém referência para evitar unused variable em alguns linters
        unset($adminId);
    }
}
