<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $postId)
    {
        $user = Auth::user();

        $comments = Comment::with(['user:id,name,email,profile_photo_url'])->where('post_id', $postId);

        if ($request->has('search')) {
            $search = $request->input('search');
            $comments = $comments->where('comment_text', 'like', "%$search%");
        }

        $comments = $comments->orderBy('created_at', 'desc');

        $comments = $comments->get();

        return $this->sendSuccessApiResponse('Comments retrieved successfully.', $comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorApiResponse($validator->errors(), 422, $validator->errors());
        }

        $validated = $validator->validated();

        $post = Post::find($postId);
        if (!$post) {
            return $this->sendErrorApiResponse('Post not found.', 404);
        }

        try {
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'post_id' => $postId,
                'comment_text' => $validated['comment_text'],
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorApiResponse('Comment addition failed.', 500);
        }

        return $this->sendSuccessApiResponse('Comment added successfully.', $comment, 201);
    }

}
