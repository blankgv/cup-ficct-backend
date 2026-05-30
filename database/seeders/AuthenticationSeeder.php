<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authentication\Authorization\Permission as Perm;
use App\Modules\Authentication\Authorization\Role as RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Permisos, roles y usuarios de prueba.
class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'api';

        // Permisos.
        foreach (Perm::all() as $name) {
            Permission::findOrCreate($name, $guard);
        }

        // Roles y sus permisos.
        $admin = Role::findOrCreate(RoleName::ADMINISTRADOR, $guard);
        $admin->givePermissionTo(Perm::all());

        $coord = Role::findOrCreate(RoleName::COORDINADOR, $guard);
        $coord->givePermissionTo([
            Perm::ACADEMIC_MANAGE,
            Perm::APPLICANT_MANAGE,
            Perm::APPLICANT_VERIFY,
            Perm::APPLICANT_ASSIGN,
            Perm::REPORT_VIEW,
        ]);

        $docente = Role::findOrCreate(RoleName::DOCENTE, $guard);
        $docente->givePermissionTo([
            Perm::GRADE_MANAGE,
            Perm::ATTENDANCE_MANAGE,
        ]);

        // POSTULANTE: sin permisos administrativos por ahora.
        Role::findOrCreate(RoleName::POSTULANTE, $guard);

        // Usuarios de prueba (password: password).
        if (User::count() === 0) {
            $password = Hash::make('password');

            $users = [
                ['name' => 'Administrador CUP FICCT', 'email' => 'admin@cup-ficct.local',       'role' => RoleName::ADMINISTRADOR],
                ['name' => 'Coordinador Académico',   'email' => 'coordinador@cup-ficct.local', 'role' => RoleName::COORDINADOR],
                ['name' => 'Docente Demo',             'email' => 'docente@cup-ficct.local',     'role' => RoleName::DOCENTE],
                ['name' => 'Postulante Demo',          'email' => 'postulante@cup-ficct.local',  'role' => RoleName::POSTULANTE],
            ];

            foreach ($users as $u) {
                $user = User::create([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => $password,
                ]);
                $user->assignRole($u['role']);
            }

            $this->command->info('Seed: '.count($users).' usuarios con rol (password: password).');
        }
    }
}
