# CUP FICCT Backend

API REST del sistema del Curso Preuniversitario (CUP) de la FICCT: autenticación y accesos, gestión académica, admisión de postulantes, pagos, evaluación y reportes. Backend Laravel consumido por un frontend separado en Next.js.

```
Next.js  →  Laravel API  →  PostgreSQL
                         →  Cloudflare R2 (archivos)
                         →  Resend (correo)
```

Autenticación stateless con JWT: `Authorization: Bearer <token>`.

## Tecnologías

- PHP 8.4 · Laravel 13
- PostgreSQL
- JWT (`php-open-source-saver/jwt-auth`)
- RBAC propio (roles y permisos)
- Cloudflare R2 (S3-compatible) para archivos (títulos, fotos)
- Resend (SMTP) para correo
- Swagger / OpenAPI (`l5-swagger`)
- Docker · Composer

## Módulos

- **Authentication** — login JWT, usuarios (username + foto), roles y permisos, cambio/recuperación de contraseña.
- **AcademicManagement** — materias, módulos/aulas, grupos, grupo-materia, horarios (con validación de solapamiento), docentes, facultades y carreras.
- **ApplicantAdmission** — postulantes (con título en R2), convocatorias y cupos, postulación (1ra/2da opción), verificación de requisitos.
- **Payments · Evaluation · Reports** — pendientes.

## Requisitos

- Docker + Docker Compose
- PostgreSQL accesible (local o Neon/externo)
- Bucket Cloudflare R2 (para subir títulos y fotos)
- Cuenta Resend con dominio verificado (para correo real)

## Levantar el proyecto

```bash
cp .env.example .env
# completa el .env (ver variables abajo)
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

API en **http://localhost:8000/api** · healthcheck `GET /api/ping` · docs **`/api/documentation`**.

Usuario admin de prueba (seed): `gutierrez.vasquez.emanuel@gmail.com` / `password`.

## Variables de entorno

### Base de datos (PostgreSQL)
Connection string en `DATABASE_URL` (formato Laravel, **sin** `jdbc:`):
```
DB_CONNECTION=pgsql
DATABASE_URL=pgsql://usuario:password@host:5432/cup_ficct?sslmode=require
```
`host.docker.internal` apunta al PostgreSQL de la máquina host. Neon u otro externo: usar su host + `?sslmode=require`.

### JWT
```
JWT_SECRET=          # php artisan jwt:secret
JWT_TTL=60
```

### Correo (Resend / SMTP)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=<api_key_resend>
MAIL_FROM_ADDRESS=no-reply@tu-dominio-verificado   # o onboarding@resend.dev para pruebas
```
> El remitente debe ser un dominio verificado en Resend. `onboarding@resend.dev` solo entrega al correo dueño de la cuenta (útil para probar).

### Almacenamiento (Cloudflare R2, S3-compatible)
```
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
R2_BUCKET=cup-ficct
R2_REGION=auto
R2_TITULOS_PATH=titulos_bachiller   # carpeta de títulos en el bucket
R2_FOTOS_PATH=fotos_perfil          # carpeta de fotos en el bucket
```
> Las carpetas son configurables por env. Los archivos se sirven con URLs firmadas temporales (bucket privado).

### CORS
```
FRONTEND_URL=http://localhost:3000
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

## Comandos útiles

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:fresh --seed   # recrea BD + datos demo
docker compose exec app php artisan l5-swagger:generate     # regenera la documentación
docker compose exec app php artisan test                    # tests (sqlite aislado)
```

## Estructura

```
app/Modules/<Modulo>/   # Controllers, Services, Repositories, Requests, Resources, DTOs, Models, Enums
app/Support/            # utilidades (ej. HasCompositePrimaryKey)
routes/api.php          # carga rutas de cada módulo
routes/modules/         # rutas por módulo
database/seeders/       # seeders (datos base + demo)
config/cors.php · jwt.php · permission... · storage.php (carpetas R2)
```

## Despliegue (Render)

Docker. El contenedor, al arrancar, corre `migrate --force`, seeders base, genera Swagger y sirve en `$PORT`. En Render → Environment, define todas las variables de arriba (DB, JWT, MAIL, R2, CORS, `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`).
