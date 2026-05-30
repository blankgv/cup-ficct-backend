<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Usuarios de prueba del sistema.
class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() > 0) {
            $this->command->info('Usuarios ya existen, se omite.');

            return;
        }

        $password = Hash::make('password');

        $users = [
            ['name' => 'Administrador CUP FICCT', 'email' => 'admin@cup-ficct.local'],
            ['name' => 'Coordinador Académico',   'email' => 'coordinador@cup-ficct.local'],
            ['name' => 'Encargado de Pagos',      'email' => 'pagos@cup-ficct.local'],
            ['name' => 'Postulante Demo',         'email' => 'postulante@cup-ficct.local'],
        ];

        foreach ($users as $u) {
            User::create([...$u, 'password' => $password]);
        }

        $this->command->info('Seed: '.count($users).' usuarios (password: password).');
    }
}
