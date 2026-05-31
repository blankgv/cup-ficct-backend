<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Soporte mínimo de clave primaria compuesta para Eloquent.
 * El modelo debe definir $primaryKey como array y $incrementing = false.
 */
trait HasCompositePrimaryKey
{
    // El nombre de la clave es un array de columnas.
    public function getKeyName(): array
    {
        return $this->primaryKey;
    }

    // WHERE con todas las columnas de la PK (para update/delete).
    protected function setKeysForSaveQuery($query): Builder
    {
        foreach ((array) $this->getKeyName() as $key) {
            $query->where($key, '=', $this->getOriginal($key) ?? $this->getAttribute($key));
        }

        return $query;
    }

    // Mismo criterio para refresh/select de un registro concreto.
    protected function setKeysForSelectQuery($query): Builder
    {
        foreach ((array) $this->getKeyName() as $key) {
            $query->where($key, '=', $this->getOriginal($key) ?? $this->getAttribute($key));
        }

        return $query;
    }
}
