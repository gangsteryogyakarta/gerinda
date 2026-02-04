<?php

namespace App\Http\Controllers;

use App\Models\WhatsappTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppTemplateController extends Controller
{
    /**
     * List all templates
     */
    public function index(Request $request): JsonResponse
    {
        $query = WhatsappTemplate::active();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $templates = $query->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get available variables for templates
     */
    public function variables(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => WhatsappTemplate::$availableVariables,
            'categories' => WhatsappTemplate::$categories,
        ]);
    }

    /**
     * Get single template
     */
    public function show(int $id): JsonResponse
    {
        $template = WhatsappTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    /**
     * Create new template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(WhatsappTemplate::$categories)),
            'content' => 'required|string|max:4096',
            'image_url' => 'nullable|url',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();
        $validated['variables'] = $this->extractVariables($validated['content']);

        $template = WhatsappTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil dibuat',
            'data' => $template,
        ], 201);
    }

    /**
     * Update template
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $template = WhatsappTemplate::findOrFail($id);

        // Prevent editing system templates
        if ($template->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Template sistem tidak dapat diubah',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|in:' . implode(',', array_keys(WhatsappTemplate::$categories)),
            'content' => 'sometimes|string|max:4096',
            'image_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (isset($validated['content'])) {
            $validated['variables'] = $this->extractVariables($validated['content']);
        }

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil diperbarui',
            'data' => $template->fresh(),
        ]);
    }

    /**
     * Delete template
     */
    public function destroy(int $id): JsonResponse
    {
        $template = WhatsappTemplate::findOrFail($id);

        // Prevent deleting system templates
        if ($template->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Template sistem tidak dapat dihapus',
            ], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil dihapus',
        ]);
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'sample_data' => 'nullable|array',
        ]);

        // Create temporary template for preview
        $template = new WhatsappTemplate([
            'content' => $validated['content'],
        ]);

        $preview = $template->getPreview($validated['sample_data'] ?? null);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'char_count' => mb_strlen($preview),
        ]);
    }

    /**
     * Duplicate a template
     */
    public function duplicate(int $id): JsonResponse
    {
        $original = WhatsappTemplate::findOrFail($id);

        $copy = $original->replicate();
        $copy->name = $original->name . ' (Copy)';
        $copy->slug = Str::slug($copy->name) . '-' . time();
        $copy->is_system = false;
        $copy->created_by = auth()->id();
        $copy->save();

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil diduplikasi',
            'data' => $copy,
        ]);
    }

    /**
     * Extract variables used in content
     */
    protected function extractVariables(string $content): array
    {
        preg_match_all('/\{(\w+)\}/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }
}
