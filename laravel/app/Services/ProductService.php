<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProductService{
    public function getAllProducts(array $filters = []){
        $query = Product::with(['category', 'variants']);
        if(isset($filters['category_id'])){
            $query->where('category_id', $filters['category_id']);
        }
        if(isset($filters['status'])){
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function getProductById($id){
        return Product::with(['category', 'variants', 'shoe', 'cloth'])->findOrFail($id);
    }

    public function createProduct(array $data){
        DB::beginTransaction();
        try{
            $product = Product::create([
                'category_id'=>$data['category_id'],
                'name'=>$data['name'],
                'slug'=>$data['slug'],
                'product_type'=>$data['product_type'],
                'base_price'=>$data['base_price'],
                'description'=>$data['description'],
                'status'=>$data['status'] ?? 'active',
            ]);

            if($data['product_type'] ==='SHOE' && isset($data['shoe_detail'])){
                $product->shoe()->create($data['shoe_detail']);
            }elseif ($data['product_type']==='CLOTH' && isset($data['cloth_detail'])){
                $product->cloth()->create($data['cloth_detail']);
            }

            if(!empty($data['variants'])){
                foreach($data['variants'] as $variant){
                    $product->variants()->create([
                        'sku'=>$variant['sku'],
                        'color'=>$variant['color'],
                        'size'=>$variant['size'],
                        'stock_quantity'=>$variant['stock_quantity'] ??0,
                        'price_override'=>$variant['price_override'] ?? null,
                        'status'=>$variant['status'] ?? 'active'
                    ]);
                }
            }

            DB::commit();
            return $product->load(['variants', 'shoe', 'cloth']);

        }catch(Exception $e){
            DB::rollBack();
            Log::error("Create Product Faild: ". $e->getMessage());
            throw $e;
        }
    }



        public function updateProduct($id,array $data){
        DB::beginTransaction();
        try{

            $product = Product::findOrFail($id);
            $product->update([
                'category_id' => $data['category_id'] ?? $product->category_id,
                'name' => $data['name'] ?? $product->name,
                'slug' => $data['slug'] ?? $product->slug,
                'product_type' => $data['product_type'] ?? $product->product_type,
                'base_price' => $data['base_price'] ?? $product->base_price,
                'description' => $data['description'] ?? $product->description,
                'status' => $data['status'] ?? $product->status,
            ]);

            if ($data['product_type'] === 'SHOE' && isset($data['shoe_detail'])) {
                $product->shoe()->updateOrCreate([], $data['shoe_detail']);
            } elseif ($data['product_type'] === 'CLOTH' && isset($data['cloth_detail'])) {
                $product->cloth()->updateOrCreate([], $data['cloth_detail']);
            }

            DB::commit();
            return $product->load(['variants', 'shoe', 'cloth']);

        }catch(Exception $e){
            DB::rollBack();
            Log::error("Update Product Faild: ". $e->getMessage());
            throw $e;
        }
    }

    public function deleteProduct($id){
        $product = Product::findOrFail($id);
        return $product->delete();
    }
}