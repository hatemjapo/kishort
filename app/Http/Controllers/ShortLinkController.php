<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeStateRequest;
use App\Http\Requests\StoreShortLinkRequest;
use App\Http\Requests\UpdateShortLinkRequest;
use App\Http\Resources\ShortLinkResource;
use App\Models\ShortLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse as HttpFoundationJsonResponse;

class ShortLinkController extends Controller
{
    use AuthorizesRequests;

    protected const FIELD_TARGET_URL = 'target_url';
    protected const FIELD_CODE = 'code';
    protected const KEY_USER_ID = 'user_id';

    public function store(StoreShortLinkRequest $request): JsonResponse
    {
        $validatedRequest = $request->validated();

        $link = ShortLink::create([
            'code' => $this->generateCode(),
            'target_url' => $validatedRequest[self::FIELD_TARGET_URL],
            'user_id' => $request->user()->id,
            'expires_at' => now()->addMonth(),
        ]);

        return (new ShortLinkResource($link))->response()->setStatusCode(201);
    }

    public function redirect(string $code)
    {
        $link = ShortLink::where('code', $code)->first();

        if ($link === null) {
            abort(404);
        }

        if (!$link->isUsable()) {
            abort(410);
        }

        return redirect($link->target_url);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return ShortLinkResource::collection($request->user()->shortLinks);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $link = ShortLink::findOrFail($id);

        $this->authorize('view', $link);

        return new ShortLinkResource($link);
    }

    public function update(UpdateShortLinkRequest $request, int $id): JsonResponse
    {
        $validatedRequest = $request->validated();

        $link = ShortLink::findOrFail($id);

        $this->authorize('update', $link);

        $link->target_url = $validatedRequest[self::FIELD_TARGET_URL];
        $link->expires_at = now()->addMonth();
        $link->save();

        return new ShortLinkResource($link);
    }

    public function destroy(int $id): JsonResponse
    {
        $link = ShortLink::findOrFail($id);
        $this->authorize('delete', $link);

        $link->delete();
        return response()->json(null, 204);
    }

    public function changeState(ChangeStateRequest $request, int $id): ShortLinkResource
    {
        $link = ShortLink::findOrFail($id);

        $this->authorize('update', $link);

        $link->is_active = $request->validated()['is_active'];
        $link->save();

        return new ShortLinkResource($link);
    }

    private function generateCode(): String
    {
        do {
            $code = Str::random(16);
        } while (ShortLink::where('code', $code)->exists());
        
        return $code;
    }
}
