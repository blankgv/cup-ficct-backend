<?php

namespace Database\Seeders;

use App\Modules\Authentication\Authorization\Permission as Perm;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\Permission;
use App\Modules\Authentication\Models\Role;
use App\Modules\Authentication\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Permisos, roles y usuarios de prueba.
class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos.
        foreach (Perm::all() as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Roles y sus permisos.
        $this->role(RoleName::ADMINISTRADOR, 'Acceso total al sistema', Perm::all());
        $this->role(RoleName::COORDINADOR, 'Gestión académica y admisión', [
            Perm::ACADEMIC_MANAGE,
            Perm::APPLICANT_MANAGE,
            Perm::APPLICANT_VERIFY,
            Perm::APPLICANT_ASSIGN,
            Perm::REPORT_VIEW,
        ]);
        $this->role(RoleName::DOCENTE, 'Notas y asistencia', [
            Perm::GRADE_MANAGE,
            Perm::ATTENDANCE_MANAGE,
        ]);
        $this->role(RoleName::POSTULANTE, 'Acceso de postulante', []);

        // Usuarios de prueba (password: password).
        if (User::count() === 0) {
            $password = Hash::make('password');

            $users = [
                ['name' => 'Administrador CUP FICCT', 'email' => 'gutierrez.vasquez.emanuel@gmail.com', 'role' => RoleName::ADMINISTRADOR],
                ['name' => 'Coordinador Académico',   'email' => 'coordinador@cup-ficct.local', 'role' => RoleName::COORDINADOR],
                ['name' => 'Docente Demo',             'email' => 'docente@cup-ficct.local',     'role' => RoleName::DOCENTE],
                ['name' => 'Postulante Demo',          'email' => 'postulante@cup-ficct.local',  'role' => RoleName::POSTULANTE],
            ];

            foreach ($users as $u) {
                User::create([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => $password,
                ])->assignRole($u['role']);
            }

            $this->command->info('Seed: '.count($users).' usuarios con rol (password: password).');
        }
    }

    /**
     * Crea/actualiza un rol y sincroniza sus permisos.
     *
     * @param list<string> $permissions
     */
    private function role(string $name, string $description, array $permissions): void
    {
        $role = Role::firstOrCreate(['name' => $name], ['description' => $description]);
        $ids = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($ids);
    }
}
