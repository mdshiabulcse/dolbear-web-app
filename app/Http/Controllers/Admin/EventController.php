<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventProduct;
use App\Models\Product;
use App\Services\CampaignPricingService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index()
    {
        $events = Event::with(['eventProducts.product', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->orderBy('event_priority', 'asc')
            ->paginate(10);

        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        $products = Product::productPublished()
            ->userCheck()
            ->isStockOut()
            ->get();

        return view('admin.events.create', compact('products'));
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        if (config('app.demo_mode')):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        // Log incoming request for debugging
        Log::info('EventController::store - Starting event creation', [
            'request_data' => $request->except(['password']),
            'ip' => $request->ip(),
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);

        DB::beginTransaction();
        try {
            // Dynamic validation - only require date fields based on what user actually filled
            $validationRules = [
                'event_title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:events,slug',
                'description' => 'nullable|string',
                'event_priority' => 'required|integer|min:0',
                'event_type' => 'required|in:date_range,daily,recurring',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'required|in:draft,active,paused,expired,cancelled',
                'campaign_type' => 'nullable|in:product,category,brand,event',
                'default_discount' => 'nullable|numeric|min:0',
                'default_discount_type' => 'nullable|in:flat,percentage',
                'badge_text' => 'nullable|string|max:255',
                'badge_color' => 'nullable|string|max:20',
            ];

            // Only validate date/time fields that are actually filled
            if (!empty($request->event_schedule_start) || !empty($request->event_schedule_end)) {
                // User filled date range fields
                $validationRules['event_schedule_start'] = 'required|date';
                $validationRules['event_schedule_end'] = 'required|date|after:event_schedule_start';
            } elseif (!empty($request->daily_start_time) || !empty($request->daily_end_time)) {
                // User filled daily time fields
                $validationRules['daily_start_time'] = 'required|date_format:H:i';
                $validationRules['daily_end_time'] = 'required|date_format:H:i|after:daily_start_time';
            } else {
                // Neither filled - require date_range as default fallback
                $validationRules['event_schedule_start'] = 'required_if:event_type,date_range|required|date';
                $validationRules['event_schedule_end'] = 'required_if:event_type,date_range|required|date|after:event_schedule_start';
            }

            Log::info('EventController::store - Validating request', ['rules' => array_keys($validationRules)]);

            $validator = validator()->make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::warning('EventController::store - Validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check for overlapping campaigns only when activating
            if ($request->status == 'active' && $request->event_type == 'date_range') {
                Log::info('EventController::store - Checking for overlapping campaigns');
                $overlapping = Event::where('status', 'active')
                    ->where('is_active', 1)
                    ->where('event_type', 'date_range')
                    ->where(function ($q) use ($request) {
                        if ($request->event_schedule_start && $request->event_schedule_end) {
                            $q->where(function ($query) use ($request) {
                                $query->where('event_schedule_start', '<=', $request->event_schedule_end)
                                      ->where('event_schedule_end', '>=', $request->event_schedule_start);
                            });
                        }
                    })->exists();

                if ($overlapping) {
                    Log::warning('EventController::store - Overlapping campaign detected');
                    Toastr::error(__('Cannot create campaign: It overlaps with an existing active campaign. Only one campaign can be active at a time.'));
                    return back()->withInput();
                }
            }

            // Additional validation for category-based campaigns
            if ($request->campaign_type == 'category' && (!$request->has('categories') || empty($request->input('categories')))) {
                Log::warning('EventController::store - Category-based campaign without categories selected');
                Toastr::error(__('Please select at least one category for category-based campaigns.'));
                return back()->withInput();
            }

            // Additional validation for brand-based campaigns
            if ($request->campaign_type == 'brand' && (!$request->has('brands') || empty($request->input('brands')))) {
                Log::warning('EventController::store - Brand-based campaign without brands selected');
                Toastr::error(__('Please select at least one brand for brand-based campaigns.'));
                return back()->withInput();
            }

            // Get banner image data from media library if banner_image_id is provided
            $bannerImage = [];
            if ($request->banner_image_id) {
                $media = \App\Models\Media::find($request->banner_image_id);
                if ($media) {
                    $bannerImage = $media->image_variants ?? [];
                }
            }

            Log::info('EventController::store - Creating event object', [
                'title' => $request->event_title,
                'slug' => $request->slug,
                'type' => $request->event_type,
                'campaign_type' => $request->campaign_type,
                'status' => $request->status,
            ]);

            // Prepare event data - only include columns that exist
            $eventData = [
                'event_title' => $request->event_title,
                'slug' => $request->slug,
                'description' => $request->description,
                'banner_image' => $bannerImage,
                'event_priority' => $request->event_priority,
                'event_type' => $request->event_type,
                // Save date fields if provided (regardless of event_type for flexibility)
                'event_schedule_start' => $request->event_schedule_start ? Carbon::parse($request->event_schedule_start)->format('Y-m-d H:i:s') : null,
                'event_schedule_end' => $request->event_schedule_end ? Carbon::parse($request->event_schedule_end)->format('Y-m-d H:i:s') : null,
                'daily_start_time' => $request->daily_start_time ?: null,
                'daily_end_time' => $request->daily_end_time ?: null,
                'background_color' => $request->background_color,
                'text_color' => $request->text_color,
                'show_on_frontend' => $request->show_on_frontend ?? 1,
                'status' => $request->status,
                'is_active' => $request->status == 'active' ? 1 : 0,
                'created_by' => authId() ?? 1,
            ];

            // Only include banner_image_id if column exists
            if (Schema::hasColumn('events', 'banner_image_id')) {
                $eventData['banner_image_id'] = $request->banner_image_id;
            }

            // Only include campaign columns if they exist
            if (Schema::hasColumn('events', 'campaign_type')) {
                $eventData['campaign_type'] = $request->campaign_type ?? 'product';
            }
            if (Schema::hasColumn('events', 'default_discount')) {
                $eventData['default_discount'] = $request->default_discount ?? 0;
            }
            if (Schema::hasColumn('events', 'default_discount_type')) {
                $eventData['default_discount_type'] = $request->default_discount_type ?? 'percentage';
            }
            if (Schema::hasColumn('events', 'badge_text')) {
                $eventData['badge_text'] = $request->badge_text;
            }
            if (Schema::hasColumn('events', 'badge_color')) {
                $eventData['badge_color'] = $request->badge_color;
            }

            Log::info('EventController::store - Event data prepared', ['event_data' => $eventData]);

            $event = Event::create($eventData);

            Log::info('EventController::store - Event created successfully', ['event_id' => $event->id, 'created_at' => $event->created_at]);

            // Handle activation for active campaigns (single-active enforcement)
            if ($request->status == 'active') {
                Log::info('EventController::store - Activating event', ['event_id' => $event->id]);
                try {
                    $event->activate();
                    Log::info('EventController::store - Event activated successfully');
                } catch (\Exception $activateError) {
                    Log::error('EventController::store - Activation failed', ['error' => $activateError->getMessage()]);
                    // Continue even if activation fails
                }
            }

            // Add products to event if provided
            $productsData = $request->input('products', []);
            Log::info('EventController::store - Processing products', [
                'products_count' => count($productsData),
                'products_data' => $productsData
            ]);

            if (!empty($productsData) && is_array($productsData)) {
                $addedCount = 0;
                foreach ($productsData as $productData) {
                    if (isset($productData['product_id']) && !empty($productData['product_id'])) {
                        try {
                            // Validate product exists
                            $product = Product::find($productData['product_id']);
                            if (!$product) {
                                Log::warning('EventController::store - Product not found', ['product_id' => $productData['product_id']]);
                                continue;
                            }

                            $eventProduct = $event->addProduct(
                                $productData['product_id'],
                                $productData['event_price'] ?? null,
                                $productData['discount_amount'] ?? 0,
                                $productData['discount_type'] ?? 'flat',
                                $productData['product_priority'] ?? 0,
                                $productData['event_stock'] ?? null
                            );
                            $addedCount++;
                            Log::info('EventController::store - Product added to event', [
                                'event_product_id' => $eventProduct->id ?? 'updated',
                                'product_id' => $productData['product_id']
                            ]);
                        } catch (\Exception $productError) {
                            Log::error('EventController::store - Failed to add product', [
                                'product_id' => $productData['product_id'] ?? 'unknown',
                                'error' => $productError->getMessage()
                            ]);
                        }
                    }
                }
                $event->updateTotalProducts();
                Log::info('EventController::store - Products added to event', ['count' => $addedCount]);
            }

            // Handle category-based campaigns
            try {
                if ($request->campaign_type == 'category' && Schema::hasTable('event_categories')) {
                    $categories = $request->input('categories', []);
                    if (!empty($categories) && is_array($categories)) {
                        Log::info('EventController::store - Adding categories to event', [
                            'categories' => $categories,
                            'include_subcategories' => $request->input('include_subcategories', true)
                        ]);
                        foreach ($categories as $categoryId) {
                            \App\Models\EventCategory::create([
                                'event_id' => $event->id,
                                'category_id' => $categoryId,
                                'include_subcategories' => $request->input('include_subcategories', true),
                            ]);
                        }
                    }
                }

                // Handle brand-based campaigns
                if ($request->campaign_type == 'brand' && Schema::hasTable('event_brands')) {
                    $brands = $request->input('brands', []);
                    if (!empty($brands) && is_array($brands)) {
                        Log::info('EventController::store - Adding brands to event', ['brands' => $brands]);
                        foreach ($brands as $brandId) {
                            \App\Models\EventBrand::create([
                                'event_id' => $event->id,
                                'brand_id' => $brandId,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // If category/brand tables don't exist yet, log but continue
                Log::error('EventController::store - Category/Brand association failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Clear campaign pricing cache (if service exists)
            try {
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                    Log::info('EventController::store - Campaign cache cleared');
                }
            } catch (\Exception $e) {
                Log::warning('EventController::store - Failed to clear cache', ['error' => $e->getMessage()]);
            }

            DB::commit();
            Log::info('EventController::store - Transaction committed successfully', ['event_id' => $event->id]);

            Toastr::success(__('Event created successfully'));
            return redirect()->route('events');

        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            Log::error('EventController::store - Database error', [
                'error' => $qe->getMessage(),
                'sql' => $qe->getSql(),
                'bindings' => $qe->getBindings(),
                'trace' => $qe->getTraceAsString()
            ]);
            Toastr::error(__('Database error: ') . $qe->getMessage());
            return back()->withInput();

        } catch (\Illuminate\Database\MassAssignmentException $mae) {
            DB::rollBack();
            Log::error('EventController::store - Mass assignment error', [
                'error' => $mae->getMessage(),
                'trace' => $mae->getTraceAsString()
            ]);
            Toastr::error(__('Data validation error: Invalid data provided.'));
            return back()->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EventController::store - General error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Display the specified event
     */
    public function show($id)
    {
        $event = Event::with(['eventProducts.product', 'createdBy', 'updatedBy'])->findOrFail($id);
        $products = Product::productPublished()
            ->userCheck()
            ->isStockOut()
            ->with(['category'])
            ->get();
        return view('admin.events.show', compact('event', 'products'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit($id)
    {
        $event = Event::with('eventProducts.product')->findOrFail($id);
        $products = Product::productPublished()
            ->userCheck()
            ->isStockOut()
            ->get();

        return view('admin.events.edit', compact('event', 'products'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {
        if (config('app.demo_mode')):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        Log::info('EventController::update - Starting event update', [
            'event_id' => $id,
            'request_data' => $request->except(['password']),
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);

        DB::beginTransaction();
        try {
            $event = Event::findOrFail($id);
            Log::info('EventController::update - Event found', ['event_id' => $event->id]);

            // Dynamic validation - only require date fields based on what user actually filled
            $validationRules = [
                'event_title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:events,slug,' . $id,
                'description' => 'nullable|string',
                'event_priority' => 'required|integer|min:0',
                'event_type' => 'required|in:date_range,daily,recurring',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'required|in:draft,active,paused,expired,cancelled',
                'campaign_type' => 'nullable|in:product,category,brand,event',
                'default_discount' => 'nullable|numeric|min:0',
                'default_discount_type' => 'nullable|in:flat,percentage',
                'badge_text' => 'nullable|string|max:255',
                'badge_color' => 'nullable|string|max:20',
            ];

            // Only validate date/time fields that are actually filled
            if (!empty($request->event_schedule_start) || !empty($request->event_schedule_end)) {
                $validationRules['event_schedule_start'] = 'required|date';
                $validationRules['event_schedule_end'] = 'required|date|after:event_schedule_start';
            } elseif (!empty($request->daily_start_time) || !empty($request->daily_end_time)) {
                $validationRules['daily_start_time'] = 'required|date_format:H:i';
                $validationRules['daily_end_time'] = 'required|date_format:H:i|after:daily_start_time';
            }

            $validator = validator()->make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::warning('EventController::update - Validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'event_id' => $id
                ]);
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check for overlapping campaigns only when activating
            if ($request->status == 'active' && $request->event_type == 'date_range' && $event->status != 'active') {
                Log::info('EventController::update - Checking for overlapping campaigns');
                $overlapping = Event::where('status', 'active')
                    ->where('is_active', 1)
                    ->where('event_type', 'date_range')
                    ->where('id', '!=', $id)
                    ->where(function ($q) use ($request) {
                        if ($request->event_schedule_start && $request->event_schedule_end) {
                            $q->where(function ($query) use ($request) {
                                $query->where('event_schedule_start', '<=', $request->event_schedule_end)
                                      ->where('event_schedule_end', '>=', $request->event_schedule_start);
                            });
                        }
                    })->exists();

                if ($overlapping) {
                    Log::warning('EventController::update - Overlapping campaign detected');
                    Toastr::error(__('Cannot create campaign: It overlaps with an existing active campaign. Only one campaign can be active at a time.'));
                    return back()->withInput();
                }
            }

            // Get banner image data from media library if banner_image_id is provided
            $bannerImage = $event->banner_image;
            if ($request->banner_image_id) {
                $media = \App\Models\Media::find($request->banner_image_id);
                if ($media) {
                    $bannerImage = $media->image_variants ?? [];
                }
            } elseif ($request->banner_image_id === null || $request->banner_image_id === '') {
                // Clear banner if explicitly set to empty
                $bannerImage = [];
            }

            // Prepare update data - only include columns that exist
            $updateData = [
                'event_title' => $request->event_title,
                'slug' => $request->slug,
                'description' => $request->description,
                'banner_image' => $bannerImage,
                'event_priority' => $request->event_priority,
                'event_type' => $request->event_type,
                // Save date fields if provided (regardless of event_type for flexibility)
                'event_schedule_start' => $request->event_schedule_start ? Carbon::parse($request->event_schedule_start)->format('Y-m-d H:i:s') : null,
                'event_schedule_end' => $request->event_schedule_end ? Carbon::parse($request->event_schedule_end)->format('Y-m-d H:i:s') : null,
                'daily_start_time' => $request->daily_start_time ?: null,
                'daily_end_time' => $request->daily_end_time ?: null,
                'background_color' => $request->background_color,
                'text_color' => $request->text_color,
                'show_on_frontend' => $request->show_on_frontend ?? 1,
                'status' => $request->status,
                'is_active' => $request->status == 'active' ? 1 : 0,
                'updated_by' => authId() ?? 1,
            ];

            // Only include banner_image_id if column exists
            if (Schema::hasColumn('events', 'banner_image_id')) {
                $updateData['banner_image_id'] = $request->banner_image_id ?: $event->banner_image_id;
            }

            // Only include campaign columns if they exist
            if (Schema::hasColumn('events', 'campaign_type')) {
                $updateData['campaign_type'] = $request->campaign_type ?? 'product';
            }
            if (Schema::hasColumn('events', 'default_discount')) {
                $updateData['default_discount'] = $request->default_discount ?? 0;
            }
            if (Schema::hasColumn('events', 'default_discount_type')) {
                $updateData['default_discount_type'] = $request->default_discount_type ?? 'percentage';
            }
            if (Schema::hasColumn('events', 'badge_text')) {
                $updateData['badge_text'] = $request->badge_text;
            }
            if (Schema::hasColumn('events', 'badge_color')) {
                $updateData['badge_color'] = $request->badge_color;
            }

            Log::info('EventController::update - Updating event', ['event_id' => $event->id]);

            $event->update($updateData);

            Log::info('EventController::update - Event updated', [
                'event_id' => $event->id,
                'updated_at' => $event->updated_at
            ]);

            // Handle activation for active campaigns (single-active enforcement)
            $wasActive = $event->getOriginal('is_active') && $event->getOriginal('status') == 'active';
            $shouldBeActive = $request->status == 'active';

            if ($shouldBeActive && !$wasActive) {
                // Activating - use single-active logic
                Log::info('EventController::update - Activating event', ['event_id' => $event->id]);
                try {
                    $event->activate();
                } catch (\Exception $activateError) {
                    Log::error('EventController::update - Activation failed', ['error' => $activateError->getMessage()]);
                }
            }

            // Update products in event
            $productsData = $request->input('products', []);
            if (!empty($productsData) && is_array($productsData)) {
                Log::info('EventController::update - Updating products', [
                    'products_count' => count($productsData)
                ]);

                // Remove existing products not in the new list
                $newProductIds = collect($productsData)->pluck('product_id')->filter()->toArray();
                $event->eventProducts()->whereNotIn('product_id', $newProductIds)->delete();

                // Add or update products
                foreach ($productsData as $productData) {
                    if (isset($productData['product_id']) && !empty($productData['product_id'])) {
                        try {
                            $event->addProduct(
                                $productData['product_id'],
                                $productData['event_price'] ?? null,
                                $productData['discount_amount'] ?? 0,
                                $productData['discount_type'] ?? 'flat',
                                $productData['product_priority'] ?? 0,
                                $productData['event_stock'] ?? null
                            );
                        } catch (\Exception $productError) {
                            Log::error('EventController::update - Failed to add product', [
                                'product_id' => $productData['product_id'] ?? 'unknown',
                                'error' => $productError->getMessage()
                            ]);
                        }
                    }
                }
                $event->updateTotalProducts();
            }

            // Update category associations (if table exists)
            try {
                if (Schema::hasTable('event_categories')) {
                    $event->eventCategories()->delete();
                    $categories = $request->input('categories', []);
                    if ($request->campaign_type == 'category' && !empty($categories) && is_array($categories)) {
                        foreach ($categories as $categoryId) {
                            \App\Models\EventCategory::create([
                                'event_id' => $event->id,
                                'category_id' => $categoryId,
                                'include_subcategories' => $request->input('include_subcategories', true),
                            ]);
                        }
                    }
                }

                // Update brand associations (if table exists)
                if (Schema::hasTable('event_brands')) {
                    $event->eventBrands()->delete();
                    $brands = $request->input('brands', []);
                    if ($request->campaign_type == 'brand' && !empty($brands) && is_array($brands)) {
                        foreach ($brands as $brandId) {
                            \App\Models\EventBrand::create([
                                'event_id' => $event->id,
                                'brand_id' => $brandId,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // If category/brand tables don't exist yet, log but continue
                Log::error('EventController::update - Category/Brand association failed', [
                    'error' => $e->getMessage()
                ]);
            }

            // Clear campaign pricing cache (if service exists)
            try {
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            } catch (\Exception $e) {
                Log::warning('EventController::update - Failed to clear cache', ['error' => $e->getMessage()]);
            }

            DB::commit();
            Log::info('EventController::update - Transaction committed', ['event_id' => $event->id]);

            Toastr::success(__('Event updated successfully'));
            return redirect()->route('events');

        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            Log::error('EventController::update - Database error', [
                'error' => $qe->getMessage(),
                'event_id' => $id
            ]);
            Toastr::error(__('Database error: ') . $qe->getMessage());
            return back()->withInput();

        } catch (\Illuminate\Database\MassAssignmentException $mae) {
            DB::rollBack();
            Log::error('EventController::update - Mass assignment error', [
                'error' => $mae->getMessage(),
                'event_id' => $id
            ]);
            Toastr::error(__('Data validation error: Invalid data provided.'));
            return back()->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EventController::update - General error', [
                'error' => $e->getMessage(),
                'event_id' => $id
            ]);
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $event = Event::findOrFail($id);
            $event->delete();
            DB::commit();

            $response['message'] = __('Event deleted successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
            $response['title'] = __('Error');
            $response['status'] = 'error';
            return response()->json($response);
        }
    }

    /**
     * Change event status
     */
    public function statusChange(Request $request)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            // Data is nested in 'data' key by the JavaScript
            $data = $request->input('data', []);
            $id = is_array($data) ? ($data['id'] ?? null) : null;

            if (!$id) {
                // Try direct input as well for compatibility
                $id = $request->input('id');
            }

            $event = Event::findOrFail($id);
            $newStatus = $event->status == 'active' ? 'paused' : 'active';

            if ($newStatus == 'active' && $event->status == 'paused') {
                // Activating - use single-active logic
                $event->activate();
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            } elseif ($newStatus == 'paused' && $event->status == 'active') {
                // Deactivating
                $updateData = [
                    'status' => 'paused',
                    'is_active' => 0,
                    'updated_by' => authId(),
                ];

                // Only add deactivated_at if column exists
                if (Schema::hasColumn('events', 'deactivated_at')) {
                    $updateData['deactivated_at'] = Carbon::now();
                }

                $event->update($updateData);
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            }

            $response['message'] = __('Status updated successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            $response['type'] = 'events';
            DB::commit();
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
            $response['title'] = __('Error');
            $response['status'] = 'error';
            return response()->json($response);
        }
    }

    /**
     * Add product to event
     */
    public function addProduct(Request $request, $eventId)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'event_price' => 'nullable|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'discount_type' => 'required|in:flat,percentage',
                'product_priority' => 'required|integer|min:0',
                'event_stock' => 'nullable|integer|min:1',
            ]);

            $event = Event::findOrFail($eventId);
            $product = Product::findOrFail($request->product_id);

            // Calculate final event price
            $eventPrice = $request->event_price;
            if ($eventPrice === null) {
                // Calculate from discount
                if ($request->discount_type == 'percentage') {
                    $eventPrice = $product->price - ($product->price * ($request->discount_amount / 100));
                } else {
                    $eventPrice = $product->price - $request->discount_amount;
                }
            }

            // Ensure price doesn't go below 0
            $eventPrice = max(0, $eventPrice);

            // VALIDATION: Event price must be less than original product price
            if ($eventPrice >= $product->price) {
                $response['message'] = __('Event price must be less than the original product price (' . get_price($product->price) . ') to provide discount to customers.');
                $response['title'] = __('Validation Error');
                $response['status'] = 'error';
                $response['original_price'] = get_price($product->price);
                $response['calculated_event_price'] = get_price($eventPrice);
                return response()->json($response, 422);
            }

            $eventProduct = $event->addProduct(
                $request->product_id,
                $eventPrice, // Use calculated event price
                $request->discount_amount,
                $request->discount_type,
                $request->product_priority,
                $request->event_stock // Pass event stock
            );
            $event->updateTotalProducts();

            // Clear campaign cache
            try {
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            } catch (\Exception $e) {
                // Ignore
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Product added to event successfully'),
                'event_product' => $eventProduct->load('product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
            $response['title'] = __('Error');
            $response['status'] = 'error';
            return response()->json($response);
        }
    }

    /**
     * Remove product from event
     */
    public function removeProduct(Request $request, $eventId)
    {
        if (config('app.demo_mode')):
            return response()->json([
                'status' => 'error',
                'message' => __('This function is disabled in demo server.')
            ]);
        endif;

        DB::beginTransaction();
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $event = Event::findOrFail($eventId);
            $event->removeProduct($request->product_id);
            $event->updateTotalProducts();

            // Clear campaign cache
            try {
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            } catch (\Exception $e) {
                // Ignore
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Product removed from event successfully')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
            $response['title'] = __('Error');
            $response['status'] = 'error';
            return response()->json($response);
        }
    }

    /**
     * Update event product
     */
    public function updateEventProduct(Request $request, $eventId, $productId)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $request->validate([
                'event_price' => 'nullable|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'discount_type' => 'required|in:flat,percentage',
                'product_priority' => 'required|integer|min:0',
                'event_stock' => 'nullable|integer|min:1',
                'is_active' => 'required|in:0,1',
                'badge_text' => 'nullable|string|max:255',
                'badge_color' => 'nullable|string|max:20',
            ]);

            $eventProduct = EventProduct::where('event_id', $eventId)
                ->where('product_id', $productId)
                ->firstOrFail();

            $product = $eventProduct->product;

            // Calculate event price if not provided
            $eventPrice = $request->event_price;
            if ($eventPrice === null || $eventPrice === '') {
                if ($request->discount_type == 'percentage') {
                    $eventPrice = $product->price - ($product->price * ($request->discount_amount / 100));
                } else {
                    $eventPrice = $product->price - $request->discount_amount;
                }
            }
            $eventPrice = max(0, $eventPrice);

            // Prepare update data
            $updateData = [
                'event_price' => $eventPrice,
                'discount_amount' => $request->discount_amount,
                'discount_type' => $request->discount_type,
                'product_priority' => $request->product_priority,
                'is_active' => $request->is_active,
                'status' => $request->is_active ? 'active' : 'paused',
                'badge_text' => $request->badge_text,
                'badge_color' => $request->badge_color,
                'updated_by' => authId() ?? 1,
            ];

            // Add final_price if column exists
            if (Schema::hasColumn('event_products', 'final_price')) {
                $updateData['final_price'] = $eventPrice;
            }

            // Add event_stock if column exists
            if (Schema::hasColumn('event_products', 'event_stock')) {
                $updateData['event_stock'] = $request->event_stock;
            }

            $eventProduct->update($updateData);

            // Clear campaign cache
            try {
                if (class_exists(CampaignPricingService::class)) {
                    app(CampaignPricingService::class)->clearCache();
                }
            } catch (\Exception $e) {
                // Ignore
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Event product updated successfully'),
                'event_product' => $eventProduct->load('product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
            $response['title'] = __('Error');
            $response['status'] = 'error';
            return response()->json($response);
        }
    }

    /**
     * Get currently running events
     */
    public function getActiveEvents()
    {
        $events = Event::currentlyRunning()
            ->showOnFrontend()
            ->with(['activeEventProducts.product'])
            ->get();

        return response()->json([
            'status' => 'success',
            'events' => $events
        ]);
    }
}