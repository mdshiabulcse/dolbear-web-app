<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductLanguage;
use App\Models\ProductStock;
use App\Traits\SlugTrait;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Sentinel;

class ProductImport implements ToCollection, WithHeadingRow, WithChunkReading, SkipsEmptyRows, SkipsOnError, WithValidation
{
    use SlugTrait, SkipsErrors, Importable;

    public function collection(Collection $rows)
    {
        if (Sentinel::getUser()->user_type == 'seller'):
            $user_id = authId();
        else:
            $user_id = 1;
        endif;

        foreach ($rows as $row):
            // Handle empty or null values properly
            $brand_id = isset($row['brand_id']) && !empty($row['brand_id']) ? $row['brand_id'] : null;
            $category_id = isset($row['category_id']) && !empty($row['category_id']) ? $row['category_id'] : null;
            $slug = isset($row['slug']) && !empty($row['slug']) ? $row['slug'] : null;

            // Handle minimum_order_quantity - default to 1 if not provided or invalid
            $minimum_order_quantity = isset($row['minimum_order_quantity']) && !empty($row['minimum_order_quantity']) ? (int)$row['minimum_order_quantity'] : 1;

            $product = Product::create([
                            'user_id'               => $user_id,
                            'brand_id'              => $brand_id,
                            'category_id'           => $category_id,
                            'created_by'            => authId(),
                            'slug'                  => $this->getSlug($row['name'], $slug),
                            'price'                 => $row['price'],
                            'purchase_cost'         => isset($row['purchase_cost']) && !empty($row['purchase_cost']) ? $row['purchase_cost'] : 0,
                            'barcode'               => isset($row['barcode']) ? $row['barcode'] : '',
                            'video_provider'        => isset($row['video_provider']) ? $row['video_provider'] : '',
                            'video_url'             => isset($row['video_url']) ? $row['video_url'] : '',
                            'current_stock'         => $row['current_stock'],
                            'minimum_order_quantity'=> $minimum_order_quantity,
                            'is_approved'           => $user_id == 1 ? (isset($row['is_approved']) ? $row['is_approved'] : 1) : 0,
                            'is_catalog'            => isset($row['is_catalog']) ? $row['is_catalog'] : 0,
                            'external_link'         => isset($row['external_link']) ? $row['external_link'] : '',
                            'is_refundable'         => isset($row['is_refundable']) ? $row['is_refundable'] : 0,
                            'cash_on_delivery'      => isset($row['cash_on_delivery']) ? $row['cash_on_delivery'] : 0,
                            'attribute_sets'        => [],
                            'thumbnail'             => [],
                            'images'                => [],
                            'meta_image'            => [],
                            'colors'                => [],
                            'selected_variants'     => [],
                            'selected_variants_ids' => [],
                            'contact_info'          => [],
                            'status'                => 'published',
                        ]);

            ProductLanguage::create([
                'product_id'            => $product->id,
                'name'                  => $row['name'],
                'short_description'     => isset($row['short_description']) ? $row['short_description'] : '',
                'description'           => isset($row['description']) ? $row['description'] : '',
                'tags'                  => isset($row['tags']) ? $row['tags'] : '',
                'meta_title'            => isset($row['meta_title']) ? $row['meta_title'] : '',
                'meta_description'      => isset($row['meta_description']) ? $row['meta_description'] : '',
                'unit'                  => $row['unit'],
                'lang'                  => 'en',
            ]);

            // Handle SKU - convert to string if it's numeric
            $sku = isset($row['sku']) ? (string)$row['sku'] : '';

            ProductStock::create([
                'product_id'            => $product->id,
                'name'                  => '',
                'sku'                   => $sku,
                'price'                 => $row['price'],
                'current_stock'         => $row['current_stock'],
                'image'                 => [],
            ]);


        endforeach;
    }

    public function chunkSize(): int
    {
        return 2000;
    }

    public function rules(): array
    {
        return [
            '*.name'                    => 'required|string|max:255',
            '*.short_description'       => 'nullable|string',
            '*.description'             => 'nullable|string',
            '*.sku'                     => 'nullable',
            '*.tags'                    => 'nullable|string',
            '*.slug'                    => 'nullable|string|unique:products,slug',
            '*.price'                   => 'required|numeric|min:0',
            '*.purchase_cost'           => 'nullable|numeric|min:0',
            '*.unit'                    => 'required|string',
            '*.category_id'             => 'required|exists:categories,id',
            '*.brand_id'                => 'nullable|exists:brands,id',
            '*.video_provider'          => 'nullable|in:youtube,vimeo,mp4',
            '*.video_url'               => 'nullable|required_with:video_provider',
            '*.current_stock'           => 'required|integer|min:0',
            '*.minimum_order_quantity'  => 'nullable|integer|min:0',
            '*.external_link'           => 'nullable|required_if:is_catalog,1',
            '*.is_approved'             => 'nullable|in:0,1',
            '*.is_catalog'              => 'nullable|in:0,1',
            '*.is_refundable'           => 'nullable|in:0,1',
            '*.cash_on_delivery'        => 'nullable|in:0,1',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            '*.name.required'                   => 'Product name is required',
            '*.price.required'                  => 'Price is required',
            '*.price.numeric'                   => 'Price must be a number',
            '*.purchase_cost.numeric'           => 'Purchase cost must be a number',
            '*.category_id.required'            => 'Category ID is required',
            '*.category_id.exists'              => 'Category ID does not exist',
            '*.brand_id.exists'                 => 'Brand ID does not exist',
            '*.unit.required'                   => 'Unit is required',
            '*.current_stock.required'          => 'Current stock is required',
            '*.external_link.required_if'       => 'External link is required when product is a catalog',
            '*.video_url.required_with'         => 'Video URL is required when video provider is set',
        ];
    }
}
