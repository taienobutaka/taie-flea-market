<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if ($item->isFavoritedBy($user)) {
            $item->favorites()->where('user_id', $user->id)->delete();
        } else {
            $item->favorites()->create(['user_id' => $user->id]);
        }

        return redirect()->route('item.show', ['id' => $item_id]);
    }

    /**
     * AJAXでお気に入りの切り替えを行う
     */
    public function toggleAjax($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if ($item->isFavoritedBy($user)) {
            $item->favorites()->where('user_id', $user->id)->delete();
            $isFavorited = false;
        } else {
            $item->favorites()->create(['user_id' => $user->id]);
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'isFavorited' => $isFavorited
        ]);
    }
}
