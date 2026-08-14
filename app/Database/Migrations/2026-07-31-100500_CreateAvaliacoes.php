<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAvaliacoes extends Migration
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
            'cliente_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'prestador_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'nota' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
            ],
            'comentario' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('prestador_id');
        $this->forge->addForeignKey('agendamento_id', 'agendamentos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('cliente_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('prestador_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('avaliacoes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('avaliacoes', true);
    }
}
