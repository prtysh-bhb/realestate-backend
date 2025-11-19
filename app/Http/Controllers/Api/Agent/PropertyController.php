<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property; 
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    // List all properties of logged-in agent
    public function index(Request $request)
    {
        $properties = $this->propertyService->getAllPropertiesByAgent($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Properties retrieved successfully',
            'data' => [
                'properties' => $properties->items(),
                'pagination' => [
                    'total' => $properties->total(),
                    'per_page' => $properties->perPage(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {

        // Get subscription
        $subscription = auth()->user()->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'You need an active subscription to create properties',
            ], 403);
        }

        // Check property limit
        $currentPropertyCount = Property::where('agent_id', auth()->id())->count();
        
        if (!$subscription->canCreateProperty($currentPropertyCount)) {
            return response()->json([
                'success' => false,
                'message' => "You have reached your property limit of {$subscription->plan->property_limit}. Please upgrade your plan.",
                'data' => [
                    'current_count' => $currentPropertyCount,
                    'limit' => $subscription->plan->property_limit,
                    'plan_name' => $subscription->plan->name,
                ],
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|string',
            'type' => 'required|in:sale,rent',
            'property_type' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:51200',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $data = $request->except(['images', 'video','documents']);
            $data['agent_id'] = auth()->id();
            
            if ($request->hasFile('images')) {
                $imagePaths = [];
                
                foreach ($request->file('images') as $index => $image) {
                    $filename = uniqid('property_') . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('properties', $filename, 'public');
                    $imagePaths[] = $path;
                }
                
                $data['images'] = $imagePaths;
                $data['primary_image'] = $imagePaths[0] ?? null;
            }

            // Handle video upload
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $filename = uniqid('property_video_') . '.' . $video->getClientOriginalExtension();
                $path = $video->storeAs('properties/videos', $filename, 'public');
                $data['video'] = $path;
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $documentPaths = [];
                
                foreach ($request->file('documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('properties/documents', $filename, 'public');
                    
                    $documentPaths[] = [
                        'name' => $originalName,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
                
                $data['documents'] = $documentPaths;
            }

            $property = Property::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => [
                    'property' => $property
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage(),
            ], 500);
        }
}

    // View specific property
    public function show(Request $request, $id)
    {
        try {
            $property = $this->propertyService->getPropertyById($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Property retrieved successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // Update property
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'zipcode' => 'sometimes|string',
            'type' => 'sometimes|in:sale,rent',
            'property_type' => 'sometimes|string',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'area' => 'sometimes|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:51200',
            'remove_images' => 'nullable|array', // Indices of images to remove
            'remove_video' => 'nullable|boolean',
        ]);

        try {
            $property = Property::where('id', $id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            $data = $request->except(['images', 'video', 'remove_images', 'remove_video']);

            // Handle removing images
            if ($request->has('remove_images')) {
                $currentImages = $property->images ?? [];
                $removeIndices = $request->remove_images;
                
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        // Delete file from storage
                        Storage::disk('public')->delete($currentImages[$index]);
                        unset($currentImages[$index]);
                    }
                }
                
                $data['images'] = array_values($currentImages); // Re-index array
                
                // Update primary image if removed
                if ($property->primary_image && !in_array($property->primary_image, $data['images'])) {
                    $data['primary_image'] = $data['images'][0] ?? null;
                }
            }

            // Handle removing video
            if ($request->has('remove_video') && $request->remove_video) {
                if ($property->video) {
                    Storage::disk('public')->delete($property->video);
                    $data['video'] = null;
                }
            }

            // Handle adding new images
            if ($request->hasFile('images')) {
                $currentImages = $data['images'] ?? $property->images ?? [];
                
                foreach ($request->file('images') as $image) {
                    $filename = uniqid('property_') . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('properties', $filename, 'public');
                    $currentImages[] = $path;
                }
                
                $data['images'] = $currentImages;
                
                // Set primary if none exists
                if (!$property->primary_image && !empty($data['images'])) {
                    $data['primary_image'] = $data['images'][0];
                }
            }

            // Handle uploading new video
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($property->video) {
                    Storage::disk('public')->delete($property->video);
                }
                
                $video = $request->file('video');
                $filename = uniqid('property_video_') . '.' . $video->getClientOriginalExtension();
                $path = $video->storeAs('properties/videos', $filename, 'public');
                $data['video'] = $path;
            }

            $property->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => [
                    'property' => $property->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete property video
     */
    public function deleteVideo($id)
    {
        try {
            $property = Property::where('id', $id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            if (!$property->video) {
                return response()->json([
                    'success' => false,
                    'message' => 'No video found for this property',
                ], 404);
            }

            // Delete video file
            Storage::disk('public')->delete($property->video);

            // Update database
            $property->update(['video' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete property
    public function destroy(Request $request, $id)
    {
        try {
            $this->propertyService->deleteProperty($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get property analytics
     */
    public function analytics(Request $request, $id)
    {
        try {
            $property = Property::where('id', $id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            // Total views
            $totalViews = $property->views()->count();

            // Views this month
            $thisMonthViews = $property->views()
                ->whereMonth('viewed_at', now()->month)
                ->whereYear('viewed_at', now()->year)
                ->count();

            // Views today
            $todayViews = $property->views()
                ->whereDate('viewed_at', today())
                ->count();

            // Views last 7 days
            $last7DaysViews = $property->views()
                ->where('viewed_at', '>=', now()->subDays(7))
                ->count();

            // Views by date (last 30 days)
            $viewsByDate = $property->views()
                ->where('viewed_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Unique visitors
            $uniqueVisitors = $property->views()
                ->distinct('ip_address')
                ->count('ip_address');

            // Registered vs Guest views
            $registeredViews = $property->views()
                ->whereNotNull('user_id')
                ->count();
            $guestViews = $property->views()
                ->whereNull('user_id')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Property analytics retrieved successfully',
                'data' => [
                    'property' => [
                        'id' => $property->id,
                        'title' => $property->title,
                    ],
                    'analytics' => [
                        'total_views' => $totalViews,
                        'this_month_views' => $thisMonthViews,
                        'today_views' => $todayViews,
                        'last_7_days_views' => $last7DaysViews,
                        'unique_visitors' => $uniqueVisitors,
                        'registered_views' => $registeredViews,
                        'guest_views' => $guestViews,
                        'views_by_date' => $viewsByDate,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Mark property as featured (with subscription check)
     */
    public function markAsFeatured($id)
    {
        try {
            $property = Property::where('agent_id', auth()->id())
                ->findOrFail($id);

            // Get subscription
            $subscription = auth()->user()->activeSubscription()->with('plan')->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active subscription to feature properties',
                ], 403);
            }

            // Check featured limit
            $featuredCheck = $subscription->canFeatureProperty();

            if (!$featuredCheck['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => "You have reached your featured property limit of {$featuredCheck['limit']} for this month.",
                    'data' => [
                        'used' => $featuredCheck['used'],
                        'limit' => $featuredCheck['limit'],
                        'remaining' => $featuredCheck['remaining'],
                        'plan_name' => $subscription->plan->name,
                    ],
                ], 403);
            }

            // Mark as featured
            $property->update([
                'is_featured' => true,
                'featured_until' => now()->addDays(30), // Featured for 30 days
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property marked as featured successfully',
                'data' => [
                    'property' => $property,
                    'featured_remaining' => $featuredCheck['remaining'] === 'unlimited' ? 'unlimited' : $featuredCheck['remaining'] - 1,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove featured status
     */
    public function removeFeatured($id)
    {
        try {
            $property = Property::where('agent_id', auth()->id())
                ->findOrFail($id);

            $property->update([
                'is_featured' => false,
                'featured_until' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Featured status removed successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription info and limits
     */
    public function subscriptionInfo()
    {
        $subscription = auth()->user()->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
                'data' => [
                    'has_subscription' => false,
                ],
            ]);
        }

        $propertyLimits = $subscription->getRemainingPropertySlots();
        $featuredLimits = $subscription->canFeatureProperty();

        return response()->json([
            'success' => true,
            'data' => [
                'has_subscription' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'starts_at' => $subscription->starts_at,
                    'ends_at' => $subscription->ends_at,
                    'plan' => $subscription->plan,
                ],
                'limits' => [
                    'properties' => $propertyLimits,
                    'featured' => $featuredLimits,
                    'images_per_property' => $subscription->plan->image_limit,
                    'video_allowed' => $subscription->plan->video_allowed,
                ],
            ],
        ]);
    }
}