<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse as HttpFoundationJsonResponse;

class ShortLinkController extends Controller
{
    use AuthorizesRequests;

    protected const FIELD_TARGET_URL = 'target_url';
    protected const FIELD_CODE = 'code';
    protected const KEY_USER_ID = 'user_id';

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            self::FIELD_TARGET_URL => ['required', 'url'],
        ]);

        $link = ShortLink::create([
            'code' => Str::random(6),
            'target_url' => $validatedRequest[self::FIELD_TARGET_URL],
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'code' => $link->code,
            'short_url' => url($link->code),
            'target_url' => $link->target_url,
        ], 201);
    }

    public function redirect(string $code)
    {
        $link = ShortLink::where('code', $code)->first();

        if ($link === null) {
            abort(404);
        }

        return redirect($link->target_url);
    }

    public function index(Request $request): JsonResponse
    {
        $links = $request->user()->shortLinks;

        $outputLinks = [];

        foreach ($links as $link) {
            $outputLinks[] = [
                'target_url' => $link->target_url,
                'code' => $link->code,
                'url' => url($link->code),
            ];
        }

        return response()->json($outputLinks);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $link = ShortLink::findOrFail($id);

        $this->authorize('view', $link);

        return response()->json([
            'target_url' => $link->target_url,
            'code' => $link->code,
            'url' => url($link->code),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validatedRequest = $request->validate([
            self::FIELD_TARGET_URL => ['required', 'url'],
        ]);

        $link = ShortLink::findOrFail($id);

        $this->authorize('update', $link);

        $link->target_url = $validatedRequest[self::FIELD_TARGET_URL];
        $link->save();

        return response()->json([
            'target_url' => $link->target_url,
            'code' => $link->code,
            'url' => url($link->code),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $link = ShortLink::findOrFail($id);
        $this->authorize('delete', $link);

        $link->delete();
        return response()->json(null, 204);
    }
}
