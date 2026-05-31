<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateHorarioDTO;
use App\Modules\AcademicManagement\DTOs\UpdateHorarioDTO;
use App\Modules\AcademicManagement\Models\Horario;
use App\Modules\AcademicManagement\Repositories\HorarioRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

// Lógica de negocio de horarios (incluye validación de solapamiento).
class HorarioService
{
    public function __construct(private readonly HorarioRepository $horarios) {}

    public function list(int $grupoId, string $sigla): Collection
    {
        return $this->horarios->forGrupoMateria($grupoId, $sigla);
    }

    public function find(int $grupoId, string $sigla, int $numero): ?Horario
    {
        return $this->horarios->find($grupoId, $sigla, $numero);
    }

    public function create(CreateHorarioDTO $data): Horario
    {
        $this->guardOverlaps(
            $data->grupoId, $data->materiaSigla, $data->dia,
            $data->horaInicio, $data->horaFin,
            $data->aulaModuloNumero, $data->aulaNumero, null,
        );

        return $this->horarios->create([
            'grupo_id' => $data->grupoId,
            'materia_sigla' => $data->materiaSigla,
            'numero' => $this->horarios->nextNumero($data->grupoId, $data->materiaSigla),
            'dia' => $data->dia,
            'hora_inicio' => $data->horaInicio,
            'hora_fin' => $data->horaFin,
            'aula_modulo_numero' => $data->aulaModuloNumero,
            'aula_numero' => $data->aulaNumero,
        ]);
    }

    public function update(Horario $horario, UpdateHorarioDTO $data): Horario
    {
        $changes = $data->toArray();

        // Valores efectivos tras el cambio.
        $dia = $changes['dia'] ?? $horario->dia->value;
        $inicio = $changes['hora_inicio'] ?? $horario->hora_inicio;
        $fin = $changes['hora_fin'] ?? $horario->hora_fin;
        $aulaModulo = $changes['aula_modulo_numero'] ?? $horario->aula_modulo_numero;
        $aulaNumero = $changes['aula_numero'] ?? $horario->aula_numero;

        $this->guardOverlaps(
            $horario->grupo_id, $horario->materia_sigla, $dia, $inicio, $fin, $aulaModulo, $aulaNumero,
            ['grupo_id' => $horario->grupo_id, 'materia_sigla' => $horario->materia_sigla, 'numero' => $horario->numero],
        );

        $horario->update($changes);

        return $horario;
    }

    public function delete(Horario $horario): void
    {
        $horario->delete();
    }

    /**
     * Lanza error si el grupo o el aula ya están ocupados en ese día/hora.
     *
     * @param array{grupo_id:int,materia_sigla:string,numero:int}|null $except
     */
    private function guardOverlaps(
        int $grupoId, string $sigla, string $dia, string $inicio, string $fin,
        string $aulaModulo, int $aulaNumero, ?array $except,
    ): void {
        if (strtotime($inicio) >= strtotime($fin)) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora final debe ser mayor a la inicial.',
            ]);
        }

        $exceptNumero = $except['numero'] ?? null;

        if ($this->horarios->grupoOverlaps($grupoId, $dia, $inicio, $fin, $exceptNumero, $sigla)) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El grupo ya tiene una clase en ese día y horario.',
            ]);
        }

        if ($this->horarios->aulaOverlaps($aulaModulo, $aulaNumero, $dia, $inicio, $fin, $except)) {
            throw ValidationException::withMessages([
                'aula_numero' => 'El aula ya está ocupada en ese día y horario.',
            ]);
        }
    }
}
