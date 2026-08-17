<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ClothesVariant;
use App\Models\Product;
use App\Models\ShoesVariant;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CartService{


        protected function resolveCart(){
            if(Auth::check()){
                $cart =Cart::firstOrCreate([
                    'user_id'=>Auth::id(),
                ]);
            }else{
                $sessionId = session()->getId();
                $cart = Cart::firstOrCreate([
                    'session_id'=>$sessionId,
                    'user_id'=>null
                ]);
            }

            return $cart->load('items');
        }

        public function addItem(Product $product, array $data){
            $cart = $this->resolveCart();

            $variant = null;
            $variantType = null;
            $variantId = null;

            if($product->product_type == 'SHOE'){
                $variant = ShoesVariant::where('product_id', $product->id)
                                        ->where('size', $data['size'])
                                        ->where('color', $data['color'])
                                        ->where('stud_type', $data['stud_type'] ?? 'TF')->first();
                $variantType = 'shoe';
            }
            else{
                $variant = ClothesVariant::where('product_id', $product->id)
                                            ->where('size', $data['size'])
                                            ->where('color', $data['color'])
                                            ->first();

                $variantType = 'cloth';
            }
            if(!$variant){
                throw new Exception('Variant not found');
            }

            $price = $variant->price_override ?? $product->base_price;

            $cartItem = CartItem::where('cart_id', $cart->id)
                                ->where('product_id', $product->id)
                                ->where('variant_type', $variantType)
                                ->where('variant_id', $variant->id)
                                ->where('size', $data['size'])
                                ->where('color', $data['color'])
                                ->where('stud_type', $data['stud_type'] ?? null)
                                ->first();

            if($cartItem){
                $cartItem->quantity +=$data['quantity'];
                $cartItem->save();
            }else{
                CartItem::create([
                    'cart_id'=>$cart->id,
                    'product_id'=>$product->id,
                    'variant_type'=>$variantType,
                    'variant_id'=>$variant->id,
                    'size'=>$data['size'],
                    'color'=>$data['color'],
                    'stud_type'=>$data['stud_type'] ?? null,
                    'quantity'=>$data['quantity'],
                    'price'=>$price
                ]);
            }

            return $cart->fresh('item');
        }


        public function getCart(){
            return $this->resolveCart()->load('items.product');
        }

        public function updateQuantity($itemId, $quantity){
            $cart = $this->resolveCart();
            $item = CartItem::where('cart_id', $cart->id)
                            ->where('id', $itemId)
                            ->first();

            if(!$item){
                throw new Exception('Item not found');
            }
            if($quantity<=0){
                $item->delete();
            }
            else{
                $item->quantity = $quantity;
                $item->save();
            }


            return $cart->fresh('items');
        }


        public function removeItem($itemId){
            $cart = $this->resolveCart();
            $item =  CartItem::where('cart_id', $cart->id)
                                ->where('id', $itemId)->delete();

            return $cart->fresh('items');
        }

        public function clearCart(){
            $cart = $this->resolveCart();
            $cart->items()->delete();
            return $cart;
        }

        public function getCartTotal(){
            $cart = $this->resolveCart();
            return $cart->items->sum(function ($item){
                return $item->price * $item->quantity;
            });
        }

    public function getCartCount()
    {
        $cart = $this->getCart();
        return $cart->items->sum('quantity');
    }


}