<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\ContentService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->list(News::class);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'nullable|boolean'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $news = $this->service->create(
            News::class,
            $data,
            'image',
            'news' // FOLDER NAME: uploads/news
        );

        return response()->json([
            'success' => true,
            'message' => 'News created successfully',
            'data' => $news
        ]);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'nullable|boolean'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $news = $this->service->update(
            News::class,
            $id,
            $data,
            'image',
            'news' // folder
        );

        return response()->json([
            'success' => true,
            'message' => 'News updated successfully',
            'data' => $news
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete(
            News::class,
            $id,
            'image',
            'news'
        );

        return response()->json([
            'success' => true,
            'message' => 'News deleted successfully'
        ]);
    }

    public function updateStatus($id)
    {
        $result = $this->service->updateStatus(News::class, $id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }
}
