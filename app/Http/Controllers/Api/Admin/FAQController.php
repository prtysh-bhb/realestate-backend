<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Services\ContentService;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    protected $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->list(FAQ::class);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'status'   => 'nullable|boolean',
        ]);

        $faq = $this->service->create(
            FAQ::class,
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully',
            'data' => $faq
        ]);
    }

    public function show($id)
    {
        $faq = FAQ::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faq
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'status'   => 'nullable|boolean',
        ]);

        $faq = $this->service->update(
            FAQ::class,
            $id,
            $data
        );

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully',
            'data' => $faq
        ]);
    }

    public function destroy($id)
    {
        $deleted = $this->service->delete(
            FAQ::class,
            $id
        );

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully'
        ]);
    }

    public function updateStatus($id)
    {
        $result = $this->service->updateStatus(Faq::class, $id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }
}
