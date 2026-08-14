<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerfisPrestador extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'tipos_servico' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'comment'    => 'CSV: diarista,passeador,telhado,piscinas,jardins,hidraulico',
            ],
            'bio' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'valor_diaria' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_passeio' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_telhado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_piscina' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_jardim' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'valor_hidraulico' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'cidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bairro' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'avaliacao_media' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
                'default'    => 0,
            ],
            'total_avaliacoes' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'disponivel' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addUniqueKey('usuario_id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('perfis_prestador', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('perfis_prestador', true);
    }
}
