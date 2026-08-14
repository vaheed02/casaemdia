<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagamentos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'agendamento_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'mock',
            ],
            'gateway_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'autorizado', 'capturado', 'estornado', 'falhou'],
                'default'    => 'pendente',
            ],
            'valor_bruto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'valor_comissao' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_liquido_prestador' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'autorizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'capturado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('agendamento_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('agendamento_id', 'agendamentos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pagamentos', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pagamentos', true);
    }
}
