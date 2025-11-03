<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReactionController extends Controller
{

    /**
     * Store post like or dislike reaction.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $reaction_type - required, string 'like' or 'dislike'
     * @param  string  $postId - UUID of the post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function postReaction(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'reaction_type' => 'required|string|in:like,dislike',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorApiResponse($validator->errors(), 422, $validator->errors());
        }

        $validated = $validator->validated();
    
        try {
            $reaction = Reaction::updateOrCreate([
                'user_id' => Auth::id(),
                'post_id' => $postId,
            ], [
                'is_like' => strtolower(trim($validated['reaction_type'])) == 'like' ? 1 : 0,
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorApiResponse('Reaction addition failed.', 500);
        }

        return $this->sendSuccessApiResponse('Reaction added successfully.', $reaction, 201);
    }
}
