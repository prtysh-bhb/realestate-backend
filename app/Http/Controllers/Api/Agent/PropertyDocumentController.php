<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyDocumentController extends Controller
{
    /**
     * Upload documents
     */
    public function upload(Request $request, $propertyId)
    {
        $request->validate([
            'documents' => 'required|array|max:10',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        try {
            $currentDocuments = $property->documents ?? [];
            
            foreach ($request->file('documents') as $file) {
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("properties/{$propertyId}/documents", $filename, 'public');
                
                $currentDocuments[] = [
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
            
            $property->update(['documents' => $currentDocuments]);

            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded successfully',
                'data' => [
                    'documents' => $property->document_urls
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all documents
     */
    public function index($propertyId)
    {
        $property = Property::findOrFail($propertyId);

        return response()->json([
            'success' => true,
            'data' => [
                'documents' => $property->document_urls
            ]
        ]);
    }

    /**
     * Delete document
     */
    public function destroy(Request $request, $propertyId)
    {
        $request->validate([
            'index' => 'required|integer',
        ]);

        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        try {
            $documents = $property->documents ?? [];
            $index = $request->index;

            if (!isset($documents[$index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                ], 404);
            }

            // Delete file from storage
            Storage::disk('public')->delete($documents[$index]['path']);

            // Remove from array
            unset($documents[$index]);
            $documents = array_values($documents); // Re-index

            $property->update(['documents' => $documents]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage(),
            ], 500);
        }
    }
}