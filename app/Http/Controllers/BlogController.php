<?php

namespace App\Http\Controllers;

use App\Http\Resources\Blog\IndexCategoryBlogResource;
use App\Http\Resources\Blog\IndexOnePostsResource;
use App\Models\Blog;
use App\Models\CategoryBlog;

class BlogController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Blog"},
     *     path="/blog",
     *     summary="Get all post group by category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all post group by category success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return mixed
     */
    public function index()
    {
        $allCategoryBlog = CategoryBlog::withAndWhereHas('posts', function ($query) {
            $query->where('status', config('app.post_status_published'));
            })->get();

        return IndexCategoryBlogResource::collection($allCategoryBlog);
    }

    /**
     * @OA\Get(
     *     tags={"Blog"},
     *     path="/blog/{slug}",
     *     summary="Get one post",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Post slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get one post success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param Blog $post
     * @return IndexOnePostsResource
     */
    public function show(Blog $post)
    {
        return IndexOnePostsResource::make($post);
    }

}
