# CUP FICCT Backend

API REST del sistema **CUP FICCT** (cursos, admisión, pagos, evaluación, reportes). Backend Laravel consumido por un frontend separado en Next.js.

```
Next.js  →  Laravel API  →  PostgreSQL
```

Autenticación stateless con JWT: `Authorization: Bearer <token>`.

## Tecnologías

- PHP 8.4 · Laravel 13
- PostgreSQL
- JWT (`php-open-source-saver/jwt-auth`)
- Docker · Composer

## Levantar el proyecto

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate
```

API en **http://localhost:8000/api** · healthcheck `GET /api/ping`.

> La conexión a la BD usa `DATABASE_URL` en `.env` (formato `pgsql://user:pass@host:puerto/db`, **sin** `jdbc:`). `host.docker.internal` apunta al PostgreSQL de la máquina host.

## Comandos útiles

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed
```

## Estructura

```
app/Modules/        # lógica por dominio (Authentication = referencia)
routes/api.php      # carga rutas de cada módulo
routes/modules/     # rutas por módulo
database/seeders/   # un seeder por módulo
config/cors.php     # CORS para el frontend
config/jwt.php      # config JWT
```