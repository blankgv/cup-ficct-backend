<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Llama a los seeders de cada módulo.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthenticationSeeder::class,
            AcademicManagementSeeder::class,
            ApplicantAdmissionSeeder::class,
            PaymentsSeeder::class,
            EvaluationSeeder::class,
            ReportsSeeder::class,
        ]);
    }
}
