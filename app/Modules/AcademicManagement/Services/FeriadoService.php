<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\Models\Feriado;
use App\Modules\AcademicManagement\Repositories\FeriadoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

// Lógica de feriados: CRUD + importación desde la API.
class FeriadoService
{
    public function __construct(private readonly FeriadoRepository $feriados) {}

    public function list(?string $gestion, int $perPage = 50): LengthAwarePaginator
    {
        return $this->feriados->paginate($gestion, $perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Feriado
    {
        return $this->feriados->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Feriado $feriado, array $data): Feriado
    {
        $feriado->update($data);

        return $feriado;
    }

    public function delete(Feriado $feriado): void
    {
        $feriado->delete();
    }

    /**
     * Importa los feriados de una gestión desde la API (la facultad luego puede editar).
     *
     * @return array<string, int>
     */
    public function importar(string $gestion): array
    {
        $base = (string) config('services.feriados.api_url');
        $pais = (string) config('services.feriados.pais');

        if ($base === '' || $pais === '') {
            throw ValidationException::withMessages([
                'api' => 'La API de feriados no está configurada (FERIADOS_API_URL / FERIADOS_PAIS).',
            ]);
        }

        $url = rtrim($base, '/')."/{$gestion}/{$pais}";
        $items = Http::get($url)->throw()->json();

        $importados = 0;
        foreach ((array) $items as $item) {
            $fecha = $item['date'] ?? null;
            if ($fecha === null) {
                continue;
            }

            $descripcion = $item['localName'] ?? $item['name'] ?? 'Feriado';
            $this->feriados->upsert($fecha, $descripcion, $gestion);
            $importados++;
        }

        return ['importados' => $importados];
    }
}
