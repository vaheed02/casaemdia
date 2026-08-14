<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Mantido por compatibilidade — use DemoSeeder.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('DemoSeeder');
    }
}
