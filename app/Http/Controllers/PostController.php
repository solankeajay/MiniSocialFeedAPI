<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the logged in user post resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * $search - optional, string to search in post content
     * $limit - optional, integer number of records to return
     * $page - optional, integer page number for pagination
     * 
     * 
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $posts = Post::with(['user:id,name,profile_photo_url'])
            // Counts for likes and comments
            ->withCount(['reactions as like_count', 'comments as comment_count'])

            // Uncomment if ned only count of likes not dislike
            // ->withCount([
            //     'reactions as like_count' => function ($query) {
            //         $query->where('is_like', 1);
            //     },
            //     'comments as comment_count'
            // ])

            // Check if current user liked the post
            ->addSelect([
                'is_liked' => DB::table('reactions')
                    ->selectRaw('count(*)')
                    ->whereColumn('reactions.post_id', 'posts.id')
                    ->where('reactions.user_id', $user->id)
                    ->limit(1),
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('content', 'like', "%{$search}%");
            });


        if ($request->has('limit')) {
            $totalRecords = (int) $request->input('limit');
            $take = $totalRecords;
        } else {
            $take = 10;
        }

        $posts = $posts->where('user_id', $user->id)->orderBy('created_at', 'desc');

        $posts = $posts->paginate($take);





        return $this->sendSuccessApiResponse('Posts retrieved successfully.', $posts);
    }


    /**
     * Display a listing of the all post feed resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * $search - optional, string to search in post content
     * $limit - optional, integer number of records to return
     * $page - optional, integer page number for pagination
     * 
     * 
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function postFeed(Request $request)
    {
        $user = Auth::user();

        $posts = Post::with(['user:id,name,profile_photo_url'])
            // Counts for likes and comments
            ->withCount(['reactions as like_count', 'comments as comment_count'])

            // Uncomment if ned only count of likes not dislike
            // ->withCount([
            //     'reactions as like_count' => function ($query) {
            //         $query->where('is_like', 1);
            //     },
            //     'comments as comment_count'
            // ])


            // Check if current user liked the post
            ->addSelect([
                'is_liked' => DB::table('reactions')
                    ->selectRaw('count(*)')
                    ->whereColumn('reactions.post_id', 'posts.id')
                    ->where('reactions.user_id', $user->id)
                    // ->where('reactions.is_like', true)
                    ->limit(1),
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('content', 'like', "%{$search}%");
            });


        if($request->has('limit')) {
            $totalRecords = (int) $request->input('limit');
            $take = $totalRecords;
        }else {
            $take = 10;
        }
        
        $posts = $posts->orderBy('created_at', 'desc');

        $posts = $posts->paginate($take);

        return $this->sendSuccessApiResponse('Feed retrieved successfully.', $posts);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * $content - required, string content of the post
     * $media_file - optional, file media file to upload
     * 
     * 
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorApiResponse($validator->errors(), 422, $validator->errors());
        }

        $validated = $validator->validated();

        $user = Auth::user();

        try{

            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('media', $filename, 'public');

                $validated['media_path'] = 'storage/' . $path;
            }

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $validated['content'],
                'media_path' => $validated['media_path'] ?? null,
            ]);

            return $this->sendSuccessApiResponse('Post created successfully.', $post, 201);
        
        } catch (\Exception $e) {
            return $this->sendErrorApiResponse('Post creation failed.', 500);
        }
        
    }

    /**
     * Display the specified resource.
     * @param  string  $id - UUID of the post
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(string $id)
    {
        $post = Post::where('user_id', Auth::id())->find($id);

        if (!$post) {
            return $this->sendErrorApiResponse('Post not found.', 404);
        }

        return $this->sendSuccessApiResponse('Post retrieved successfully.', $post);
    }

    /**
     * Remove the specified resource from storage.
     * @param  string  $id - UUID of the post
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(string $id)
    {
        $post = Post::where('user_id', Auth::id())->find($id);
        if (!$post) {
            return $this->sendErrorApiResponse('Post not found.', 404);
        }
        try {
            $mediaPath = $post->getRawOriginal('media_path');

            if ($mediaPath && Storage::disk('public')->exists(str_replace('storage/', '', $mediaPath))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $mediaPath));
            }
            $post->delete();
            return $this->sendSuccessApiResponse('Post deleted successfully.', null);
        } catch (\Exception $e) {
            return $this->sendErrorApiResponse('Post deletion failed.', 500);
        }
    }
}
