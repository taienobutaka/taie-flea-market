<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Item;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->item_id = $item_id;
        $comment->content = $request->content;
        $comment->save();

        return redirect()->back()->with('success', 'コメントを投稿しました。');
    }
}
