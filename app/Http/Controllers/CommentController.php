<?php

namespace App\Http\Controllers;

use App\Models\CardComment;
use App\Models\SubtaskComment;
use App\Models\Card;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Card Comments (Admin and Team Lead only)
    public function storeCardComment(Request $request, Card $card)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'team_lead'])) {
            abort(403, 'Only admins and team leads can comment on cards');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|integer',
        ]);

        $parentId = $validated['parent_comment_id'] ?? null;
        if ($parentId) {
            CardComment::where('comment_id', $parentId)
                ->where('card_id', $card->card_id)
                ->firstOrFail();
        }

        CardComment::create([
            'card_id' => $card->card_id,
            'user_id' => $user->user_id,
            'comment' => $validated['comment'],
            'parent_comment_id' => $parentId,
        ]);

        return back()->with('success', 'Comment added successfully');
    }

    public function updateCardComment(Request $request, CardComment $comment)
    {
        $user = Auth::user();
        
        if ($user->user_id !== $comment->user_id) {
            abort(403, 'You can only edit your own comments');
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment->update([
            'comment' => $request->comment
        ]);

        return back()->with('success', 'Comment updated successfully');
    }

    public function deleteCardComment(CardComment $comment)
    {
        $user = Auth::user();
        
        if ($user->user_id !== $comment->user_id && !in_array($user->role, ['admin'])) {
            abort(403, 'You can only delete your own comments');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully');
    }

    // Subtask Comments (All users)
    public function storeSubtaskComment(Request $request, Subtask $subtask)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|integer',
        ]);

        $parentId = $validated['parent_comment_id'] ?? null;
        if ($parentId) {
            SubtaskComment::where('comment_id', $parentId)
                ->where('subtask_id', $subtask->subtask_id)
                ->firstOrFail();
        }

        SubtaskComment::create([
            'subtask_id' => $subtask->subtask_id,
            'user_id' => Auth::user()->user_id,
            'comment' => $validated['comment'],
            'parent_comment_id' => $parentId,
        ]);

        return back()->with('success', 'Comment added successfully');
    }

    public function updateSubtaskComment(Request $request, SubtaskComment $comment)
    {
        $user = Auth::user();
        
        if ($user->user_id !== $comment->user_id) {
            abort(403, 'You can only edit your own comments');
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment->update([
            'comment' => $request->comment
        ]);

        return back()->with('success', 'Comment updated successfully');
    }

    public function deleteSubtaskComment(SubtaskComment $comment)
    {
        $user = Auth::user();
        
        if ($user->user_id !== $comment->user_id && !in_array($user->role, ['admin'])) {
            abort(403, 'You can only delete your own comments');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully');
    }
}
