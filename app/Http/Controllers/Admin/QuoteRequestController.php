<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = QuoteRequest::query()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(max(1, min(100, $request->integer('per_page', 15))))
            ->withQueryString();

        return response()->json($items);
    }

    public function show(int|string $id): JsonResponse
    {
        return response()->json(
            QuoteRequest::query()->with(['items.product', 'attachments.media'])->findOrFail($id)
        );
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'is_read' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $quote = QuoteRequest::query()->findOrFail($id);
        $quote->fill($validated)->save();

        return response()->json($quote->fresh(['items.product', 'attachments.media']));
    }

    public function destroy(int|string $id): JsonResponse
    {
        QuoteRequest::query()->findOrFail($id)->delete();

        return response()->json(['deleted' => true]);
    }
}
