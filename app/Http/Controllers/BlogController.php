<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class BlogController extends Controller
{
    /**
     * List all active blogs with pagination.
     */
    public function index(Request $request)
    {
        try {
            $qry = Blog::where('status', 'active');

            // Search by title or publisher
            if ($request->filled('search')) {
                $search = $request->search;
                $qry->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('publisher', 'LIKE', "%{$search}%");
                });
            }

            // Ordering
            $order = $request->input('order', 'desc');
            $qry->orderBy('created_at', $order === 'asc' ? 'asc' : 'desc');

            // Pagination
            $perPage = $request->page === '0' ? $qry->count() : $request->input('per_page', 10);
            $data = $qry->paginate($perPage);

            $blogs = $data->getCollection()->map(function ($blog) {
                return [
                    'id'         => $blog->id,
                    'title'      => $blog->title,
                    'description' => $blog->description,
                    'image'      => $blog->image ? asset('storage/' . $blog->image) : null,
                    'publisher'  => $blog->publisher,
                    'date'       => $blog->date,
                    'status'     => $blog->status,
                ];
            });

            return response()->json([
                'pagination' => [
                    'limit_page' => $perPage,
                    'total_count' => $data->total(),
                    'total_page' => $data->lastPage(),
                    'current_page' => $data->currentPage(),
                    'next_page'  => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                    'previous_page' => $data->onFirstPage() ? null : $data->currentPage() - 1,
                ],
                'message' => 'Blogs retrieved successfully',
                'data' => $blogs,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show a single blog by ID.
     */
    public function show($id)
    {
        try {
            $blog = Blog::where('status', 'active')->findOrFail($id);

            return response()->json([
                'message' => 'Blog retrieved successfully',
                'data' => [
                    'id'          => $blog->id,
                    'title'       => $blog->title,
                    'description' => $blog->description,
                    'image'       => $blog->image ? asset('storage/' . $blog->image) : null,
                    'publisher'   => $blog->publisher,
                    'date'        => $blog->date,
                    'status'      => $blog->status,
                    'created_at'  => $blog->created_at,
                    'updated_at'  => $blog->updated_at,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Blog not found or inactive.'], 404);
        }
    }

    /**
     * Create a new blog.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'publisher'   => 'nullable|string|max:255',
                'date'        => 'nullable|date',
                'status'      => 'nullable|in:active,inactive,pending',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('blogs', 'public');
            }

            $blog = Blog::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image'       => $imagePath,
                'publisher'   => $validated['publisher'] ?? null,
                'date'        => $validated['date'] ?? null,
                'status'      => $validated['status'] ?? 'active',
            ]);

            return response()->json([
                'message' => 'Blog created successfully',
                'data' => [
                    'id'          => $blog->id,
                    'title'       => $blog->title,
                    'description' => $blog->description,
                    'image'       => $blog->image ? asset('storage/' . $blog->image) : null,
                    'publisher'   => $blog->publisher,
                    'date'        => $blog->date,
                    'status'      => $blog->status,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a blog.
     */
    public function update(Request $request, $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'publisher'   => 'nullable|string|max:255',
                'date'        => 'nullable|date',
                'status'      => 'nullable|in:active,inactive,pending',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image
                if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                    Storage::disk('public')->delete($blog->image);
                }
                $blog->image = $request->file('image')->store('blogs', 'public');
            }

            $blog->update($validated);

            return response()->json([
                'message' => 'Blog updated successfully',
                'data' => [
                    'id'          => $blog->id,
                    'title'       => $blog->title,
                    'description' => $blog->description,
                    'image'       => $blog->image ? asset('storage/' . $blog->image) : null,
                    'publisher'   => $blog->publisher,
                    'date'        => $blog->date,
                    'status'      => $blog->status,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete a blog.
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);

            $blog->delete();

            return response()->json(['message' => 'Blog deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Blog not found or cannot be deleted.'], 404);
        }
    }
}
