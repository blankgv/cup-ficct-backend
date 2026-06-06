<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role;
use App\Modules\Authentication\DTOs\CreateUserDTO;
use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Services\UserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Carga masiva de postulantes desde CSV. Crea su usuario (rol POSTULANTE).
class BatchPostulanteService
{
    /** Columnas esperadas en el CSV (en este orden no es obligatorio; se usan los encabezados). */
    private const COLUMNAS = ['documento', 'nombres', 'apellidos', 'email', 'fecha_nacimiento', 'colegio', 'ciudad', 'telefono'];

    public function __construct(private readonly UserService $users) {}

    /**
     * Procesa el CSV: inserta filas válidas, salta inválidas/duplicadas y reporta.
     *
     * @return array{creados:int, omitidos:int, errores:list<array{fila:int, error:string}>}
     */
    public function import(UploadedFile $archivo): array
    {
        $filas = $this->leerCsv($archivo);

        $creados = 0;
        $omitidos = 0;
        $errores = [];

        foreach ($filas as $i => $fila) {
            $numeroFila = $i + 2; // +1 encabezado, +1 base 1

            $validator = Validator::make($fila, $this->reglas());
            if ($validator->fails()) {
                $omitidos++;
                $errores[] = ['fila' => $numeroFila, 'error' => $validator->errors()->first()];

                continue;
            }

            if ($this->duplicado($fila)) {
                $omitidos++;
                $errores[] = ['fila' => $numeroFila, 'error' => 'Documento o correo ya registrado.'];

                continue;
            }

            DB::transaction(function () use ($fila) {
                $user = $this->users->create(new CreateUserDTO(
                    email: $fila['email'],
                    password: $fila['documento'],
                    role: Role::POSTULANTE,
                ));

                Postulante::create([
                    'documento' => $fila['documento'],
                    'nombres' => $fila['nombres'],
                    'apellidos' => $fila['apellidos'],
                    'email' => $fila['email'],
                    'telefono' => $fila['telefono'] ?: null,
                    'fecha_nacimiento' => $fila['fecha_nacimiento'],
                    'colegio' => $fila['colegio'],
                    'ciudad' => $fila['ciudad'],
                    'user_id' => $user->id,
                ]);
            });

            $creados++;
        }

        return ['creados' => $creados, 'omitidos' => $omitidos, 'errores' => $errores];
    }

    /**
     * @return list<array<string, string>>
     */
    private function leerCsv(UploadedFile $archivo): array
    {
        $contenido = file_get_contents($archivo->getRealPath());
        $lineas = preg_split('/\r\n|\r|\n/', trim((string) $contenido));

        if ($lineas === false || count($lineas) < 2) {
            return [];
        }

        $encabezados = array_map(fn ($h) => trim(strtolower($h)), str_getcsv(array_shift($lineas)));

        $filas = [];
        foreach ($lineas as $linea) {
            if (trim($linea) === '') {
                continue;
            }
            $valores = str_getcsv($linea);
            $fila = [];
            foreach (self::COLUMNAS as $col) {
                $idx = array_search($col, $encabezados, true);
                $fila[$col] = $idx !== false ? trim((string) ($valores[$idx] ?? '')) : '';
            }
            $filas[] = $fila;
        }

        return $filas;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'documento' => ['required', 'string', 'max:50'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'colegio' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @param array<string, string> $fila
     */
    private function duplicado(array $fila): bool
    {
        return Postulante::query()->whereKey($fila['documento'])->exists()
            || Postulante::query()->where('email', $fila['email'])->exists()
            || User::query()->where('email', $fila['email'])->exists();
    }
}
