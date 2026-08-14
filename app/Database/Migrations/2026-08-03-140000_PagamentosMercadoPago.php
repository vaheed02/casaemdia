<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PagamentosMercadoPago extends Migration
{
    public function up(): void
    {
        $pagFields = $this->db->getFieldNames('pagamentos');

        $add = [
            'mp_preference_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'mp_payment_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'mp_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'checkout_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'payout_status' => [
                'type'       => 'ENUM',
                'constraint' => ['nao_aplicavel', 'pendente', 'processando', 'pago', 'falhou'],
                'default'    => 'nao_aplicavel',
            ],
            'payout_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'payout_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'payout_nota' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'meta_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        foreach ($add as $name => $def) {
            if (! in_array($name, $pagFields, true)) {
                $this->forge->addColumn('pagamentos', [$name => $def]);
            }
        }

        $perfilFields = $this->db->getFieldNames('perfis_prestador');
        if (! in_array('mp_email', $perfilFields, true)) {
            $this->forge->addColumn('perfis_prestador', [
                'mp_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 180,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->tableExists('configuracoes')) {
            $this->forge->addField([
                'chave' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                ],
                'valor' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('chave', true);
            $this->forge->createTable('configuracoes', true);

            $now = date('Y-m-d H:i:s');
            $this->db->table('configuracoes')->insertBatch([
                [
                    'chave'      => 'comissao_percentual',
                    'valor'      => (string) config('Servicos')->comissaoPercentual,
                    'updated_at' => $now,
                ],
                [
                    'chave'      => 'mp_auto_payout',
                    'valor'      => '0',
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        foreach ([
            'meta_json', 'payout_nota', 'payout_em', 'payout_ref', 'payout_status',
            'checkout_url', 'mp_status', 'mp_payment_id', 'mp_preference_id',
        ] as $col) {
            if (in_array($col, $this->db->getFieldNames('pagamentos'), true)) {
                $this->forge->dropColumn('pagamentos', $col);
            }
        }

        if (in_array('mp_email', $this->db->getFieldNames('perfis_prestador'), true)) {
            $this->forge->dropColumn('perfis_prestador', 'mp_email');
        }

        if ($this->db->tableExists('configuracoes')) {
            $this->forge->dropTable('configuracoes', true);
        }
    }
}
