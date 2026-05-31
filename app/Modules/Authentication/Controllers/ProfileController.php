<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\UpdateProfileRequest;
use App\Modules\Authentication\Requests\UploadFotoRequest;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\FotoPerfilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Perfil propio del usuario autenticado (username y foto).
class ProfileController extends Controller
{
    public function __construct(private readonly FotoPerfilService $fotos) {}

    #[OA\Put(
        path: '/api/auth/me/profile',
        tags: ['Auth'],
        summary: 'Actualizar mi perfil (username)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['username'],
            properties: [new OA\Property(property: 'username', type: 'string', example: 'jperez')]
        )),
        responses: [new OA\Response(response: 200, description: 'Perfil actualizado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    )]
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->update(['username' => $request->validated('username')]);

        return new UserResource($user);
    }

    #[OA\Post(
        path: '/api/auth/me/foto',
        tags: ['Auth'],
        summary: 'Subir mi foto de perfil',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['foto'],
                properties: [new OA\Property(property: 'foto', type: 'string', format: 'binary')]
            )
        )),
        responses: [new OA\Response(response: 200, description: 'Foto subida', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    )]
    public function uploadFoto(UploadFotoRequest $request): UserResource
    {
        return new UserResource($this->fotos->upload($request->user(), $request->file('foto')));
    }

    #[OA\Get(
        path: '/api/auth/me/foto',
        tags: ['Auth'],
        summary: 'Descargar mi foto (redirige a URL firmada)',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 302, description: 'Redirige a la foto'),
            new OA\Response(response: 404, description: 'Sin foto'),
        ]
    )]
    public function foto(Request $request): RedirectResponse
    {
        $url = $this->fotos->downloadUrl($request->user());

        abort_if($url === null, 404, 'No tienes foto de perfil.');

        return redirect()->away($url);
    }
}
