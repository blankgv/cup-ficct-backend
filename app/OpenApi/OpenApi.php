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
#[OA\Tag(name: 'Convocatorias', description: 'Convocatorias y cupos por carrera')]
#[OA\Schema(
    schema: 'Convocatoria',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Admisión CUP 2026-I'),
        new OA\Property(property: 'gestion', type: 'string', example: '2026'),
        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2026-01-10'),
        new OA\Property(property: 'fecha_fin', type: 'string', format: 'date', example: '2026-02-10'),
        new OA\Property(property: 'estado', type: 'string', enum: ['ABIERTA', 'CERRADA'], example: 'ABIERTA'),
    ]
)]
#[OA\Tag(name: 'Facultades', description: 'Gestión de facultades')]
#[OA\Schema(
    schema: 'Facultad',
    properties: [
        new OA\Property(property: 'codigo', type: 'string', example: '187'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Ingeniería en Ciencias de la Computación y Telecomunicaciones'),
        new OA\Property(property: 'abreviatura', type: 'string', example: 'FICCT'),
    ]
)]
#[OA\Tag(name: 'Carreras', description: 'Gestión de carreras')]
#[OA\Schema(
    schema: 'Carrera',
    properties: [
        new OA\Property(property: 'codigo', type: 'string', example: '187-09'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Ingeniería de Sistemas'),
        new OA\Property(property: 'facultad_codigo', type: 'string', example: '187'),
    ]
)]
#[OA\Tag(name: 'Postulantes', description: 'Gestión de postulantes')]
#[OA\Schema(
    schema: 'Postulante',
    properties: [
        new OA\Property(property: 'documento', type: 'string', example: '9876543'),
        new OA\Property(property: 'nombres', type: 'string', example: 'María José'),
        new OA\Property(property: 'apellidos', type: 'string', example: 'Quispe Vargas'),
        new OA\Property(property: 'email', type: 'string', example: 'mquispe@example.com'),
        new OA\Property(property: 'telefono', type: 'string', example: '70000000'),
        new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '2007-03-15'),
        new OA\Property(property: 'colegio', type: 'string', example: 'Colegio Nacional'),
    ]
)]
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
#[OA\Tag(name: 'Docentes', description: 'Gestión de docentes')]
#[OA\Schema(
    schema: 'Docente',
    properties: [
        new OA\Property(property: 'ci', type: 'string', example: '1234567'),
        new OA\Property(property: 'nombres', type: 'string', example: 'Juan Carlos'),
        new OA\Property(property: 'apellidos', type: 'string', example: 'Pérez López'),
        new OA\Property(property: 'email', type: 'string', example: 'jperez@cup-ficct.local'),
        new OA\Property(property: 'telefono', type: 'string', example: '70000000'),
        new OA\Property(property: 'profesion', type: 'string', example: 'Ing. Matemático'),
    ]
)]
#[OA\Tag(name: 'Grupos', description: 'Gestión de grupos (paralelos)')]
#[OA\Schema(
    schema: 'Grupo',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'codigo', type: 'string', example: 'A'),
        new OA\Property(property: 'turno', type: 'string', enum: ['MANANA', 'TARDE', 'NOCHE'], example: 'MANANA'),
        new OA\Property(property: 'capacidad', type: 'integer', example: 70),
        new OA\Property(property: 'gestion', type: 'string', example: '2026'),
    ]
)]
#[OA\Tag(name: 'Horarios', description: 'Horarios de grupo-materia (día, hora, aula)')]
#[OA\Schema(
    schema: 'Horario',
    properties: [
        new OA\Property(property: 'grupo_id', type: 'integer', example: 1),
        new OA\Property(property: 'materia_sigla', type: 'string', example: 'FIS'),
        new OA\Property(property: 'numero', type: 'integer', example: 1),
        new OA\Property(property: 'dia', type: 'string', enum: ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'], example: 'LUNES'),
        new OA\Property(property: 'hora_inicio', type: 'string', example: '07:00'),
        new OA\Property(property: 'hora_fin', type: 'string', example: '09:00'),
        new OA\Property(property: 'aula', type: 'object'),
    ]
)]
#[OA\Tag(name: 'Aulas', description: 'Gestión de aulas por módulo')]
#[OA\Schema(
    schema: 'Aula',
    properties: [
        new OA\Property(property: 'modulo_numero', type: 'string', example: '236'),
        new OA\Property(property: 'numero', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Laboratorio A'),
        new OA\Property(property: 'capacidad', type: 'integer', example: 40),
        new OA\Property(property: 'piso', type: 'integer', example: 2),
        new OA\Property(property: 'tipo', type: 'string', enum: ['COMUN', 'LABORATORIO', 'AUDITORIO'], example: 'LABORATORIO'),
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
