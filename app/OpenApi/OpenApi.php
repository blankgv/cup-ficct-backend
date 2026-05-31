<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'CUP FICCT Backend API', description: 'API REST del sistema de admisión al CUP FICCT.')]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Servidor del backend')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
#[OA\Tag(name: 'Auth', description: 'Sesión: login, logout, token')]
#[OA\Tag(name: 'Password', description: 'Recuperación y cambio de contraseña')]
#[OA\Tag(name: 'Users', description: 'Gestión de usuarios')]
#[OA\Tag(name: 'Roles', description: 'Gestión de roles y permisos')]
#[OA\Tag(name: 'Materias', description: 'Gestión de materias del curso')]
#[OA\Tag(name: 'Modulos', description: 'Gestión de módulos (edificios)')]
#[OA\Schema(
    schema: 'Materia',
    properties: [
        new OA\Property(property: 'sigla', type: 'string', example: 'MAT'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Matemáticas'),
        new OA\Property(property: 'peso', type: 'number', format: 'float', example: 0.25),
    ]
)]
#[OA\Schema(
    schema: 'Modulo',
    properties: [
        new OA\Property(property: 'numero', type: 'string', example: '236'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Módulo 236'),
        new OA\Property(property: 'ubicacion', type: 'string', example: 'Campus central'),
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'email', type: 'string', example: 'admin@cup-ficct.local'),
        new OA\Property(property: 'must_change_password', type: 'boolean', example: false),
        new OA\Property(property: 'role', type: 'string', example: 'ADMINISTRADOR'),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'AuthToken',
    properties: [
        new OA\Property(property: 'access_token', type: 'string'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    ]
)]
#[OA\Schema(
    schema: 'Role',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'ADMINISTRADOR'),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
#[OA\Schema(
    schema: 'Permission',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'user.manage'),
    ]
)]
class OpenApi
{
}
