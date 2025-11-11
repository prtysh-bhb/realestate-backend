<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    // Add property to favorites
    public function store(Request $request, $propertyId)
    {
        try {
            $result = $this->favoriteService->toggleFavorite(
                $request->user()->id,
                $propertyId
            );

            return response()->json([
                'success' => true,
                'message' => $result['action'] === 'added' 
                    ? 'Property added to favorites' 
                    : 'Property removed from favorites',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Remove property from favorites
    public function destroy(Request $request, $propertyId)
    {
        try {
            $this->favoriteService->removeFromFavorites(
                $request->user()->id,
                $propertyId
            );

            return response()->json([
                'success' => true,
                'message' => 'Property removed from favorites',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // List all favorites
    public function index(Request $request)
    {
        $favorites = $this->favoriteService->getUserFavorites($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Favorites retrieved successfully',
            'data' => [
                'favorites' => $favorites->items(),
                'pagination' => [
                    'total' => $favorites->total(),
                    'per_page' => $favorites->perPage(),
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                ],
            ],
        ]);
    }

    // Check if property is favorited
    public function check(Request $request, $propertyId)
    {
        $isFavorited = $this->favoriteService->checkIfFavorited(
            $request->user()->id,
            $propertyId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorited' => $isFavorited,
            ],
        ]);
    }
}