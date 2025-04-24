<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;

class PurchaseController extends Controller
{
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $profile = Profile::where('user_id', Auth::id())->first();
        return view('purchase', compact('item', 'profile'));
    }

    public function showAddressForm($item_id)
    {
        $item = Item::findOrFail($item_id);
        $profile = Profile::where('user_id', Auth::id())->first();
        return view('address', compact('item', 'profile'));
    }

    public function updateAddress(Request $request, $item_id)
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|max:8',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
        ]);

        $profile = Profile::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return redirect()->route('purchase', $item_id)
            ->with('success', '住所を更新しました。');
    }

    public function confirm(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        
        try {
            DB::beginTransaction();
            
            // 購入履歴を作成
            $purchase = Purchase::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'postal_code' => $request->postal_code,
                'status' => 'pending',
                'total_amount' => $item->price
            ]);

            // 商品の在庫を更新
            $item->update(['stock' => $item->stock - 1]);

            DB::commit();
            
            return redirect()->route('purchase.complete', $item_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '購入処理中にエラーが発生しました。');
        }
    }

    public function complete($item_id)
    {
        $item = Item::findOrFail($item_id);
        $purchase = Purchase::where('item_id', $item_id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();
            
        return view('purchase.complete', compact('item', 'purchase'));
    }
} 