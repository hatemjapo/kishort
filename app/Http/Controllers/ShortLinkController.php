<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortLinkController extends Controller
{
    protected const FIELD_TARGET_URL = 'target_url';
    protected const FIELD_CODE = 'code';

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            self::FIELD_TARGET_URL => ['required', 'url'],
        ]);

        $link = ShortLink::create([
            'code' => Str::random(6),
            'target_url' => $validatedRequest[self::FIELD_TARGET_URL],
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
}
