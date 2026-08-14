<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgendamentos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cliente_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'prestador_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'tipo_servico' => [
                'type'       => 'ENUM',
                'constraint' => ['diarista', 'passeador', 'telhado', 'piscinas', 'jardins', 'hidraulico'],
            ],
            'endereco_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'data_servico' => [
                'type' => 'DATE',
            ],
            'hora_inicio' => [
                'type' => 'TIME',
            ],
            'duracao_horas' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,1',
                'default'    => 4,
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'comissao_percentual' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 15,
            ],
            'comissao_valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_prestador' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'pendente',
                    'aceito',
                    'rejeitado',
                    'cancelado',
                    'em_andamento',
                    'aguardando_confirmacao',
                    'confirmado',
                    'pago',
                ],
                'default'    => 'pendente',
            ],
            'observacoes_cliente' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'motivo_rejeicao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'aceito_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'iniciado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'concluido_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'confirmado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'pago_em' => [
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
        $this->forge->addKey('cliente_id');
        $this->forge->addKey('prestador_id');
        $this->forge->addKey('status');
        $this->forge->addKey('data_servico');
        $this->forge->addForeignKey('cliente_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('prestador_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('endereco_id', 'enderecos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('agendamentos', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('agendamentos', true);
    }
}
