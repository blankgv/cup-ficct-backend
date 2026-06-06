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
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $filas = $this->leerArchivo($archivo);

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
     * Lee el archivo (CSV o Excel) a una matriz [encabezados, ...filas].
     *
     * @return list<array<string, string>>
     */
    private function leerArchivo(UploadedFile $archivo): array
    {
        $ext = strtolower($archivo->getClientOriginalExtension());
        $matriz = in_array($ext, ['xlsx', 'xls'], true)
            ? $this->matrizExcel($archivo)
            : $this->matrizCsv($archivo);

        return $this->filasDesdeMatriz($matriz);
    }

    /**
     * @return list<list<string>>
     */
    private function matrizCsv(UploadedFile $archivo): array
    {
        $contenido = (string) file_get_contents($archivo->getRealPath());
        $lineas = preg_split('/\r\n|\r|\n/', trim($contenido)) ?: [];

        return array_map(fn (string $l) => str_getcsv($l), array_filter($lineas, fn ($l) => trim($l) !== ''));
    }

    /**
     * @return list<list<string>>
     */
    private function matrizExcel(UploadedFile $archivo): array
    {
        $hoja = IOFactory::load($archivo->getRealPath())->getActiveSheet()->toArray();

        return array_values(array_filter(
            array_map(fn ($fila) => array_map(fn ($c) => (string) ($c ?? ''), $fila), $hoja),
            fn ($fila) => trim(implode('', $fila)) !== '',
        ));
    }

    /**
     * Mapea la matriz a filas asociativas usando la primera fila como encabezados.
     *
     * @param list<list<string>> $matriz
     * @return list<array<string, string>>
     */
    private function filasDesdeMatriz(array $matriz): array
    {
        if (count($matriz) < 2) {
            return [];
        }

        $encabezados = array_map(fn ($h) => trim(strtolower((string) $h)), array_shift($matriz));

        $filas = [];
        foreach ($matriz as $valores) {
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
