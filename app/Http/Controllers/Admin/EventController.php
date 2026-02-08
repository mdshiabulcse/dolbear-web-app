<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventProduct;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index()
    {
        $events = Event::with(['eventProducts.product', 'createdBy'])
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

        DB::beginTransaction();
        try {
            $request->validate([
                'event_title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:events,slug',
                'description' => 'nullable|string',
                'event_priority' => 'required|integer|min:0',
                'event_type' => 'required|in:date_range,daily,recurring',
                'event_schedule_start' => 'required_if:event_type,date_range|nullable|date',
                'event_schedule_end' => 'required_if:event_type,date_range|nullable|date|after:event_schedule_start',
                'daily_start_time' => 'required_if:event_type,daily|nullable|date_format:H:i',
                'daily_end_time' => 'required_if:event_type,daily|nullable|date_format:H:i|after:daily_start_time',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'required|in:draft,active,paused,expired,cancelled',
            ]);

            // Get banner image data from media library if banner_image_id is provided
            $bannerImage = [];
            if ($request->banner_image_id) {
                $media = \App\Models\Media::find($request->banner_image_id);
                if ($media) {
                    $bannerImage = $media->image_variants ?? [];
                }
            }

            $event = Event::create([
                'event_title' => $request->event_title,
                'slug' => $request->slug,
                'description' => $request->description,
                'banner_image' => $bannerImage,
                'banner_image_id' => $request->banner_image_id,
                'event_priority' => $request->event_priority,
                'event_type' => $request->event_type,
                'event_schedule_start' => $request->event_type == 'date_range' ? $request->event_schedule_start : null,
                'event_schedule_end' => $request->event_type == 'date_range' ? $request->event_schedule_end : null,
                'daily_start_time' => $request->event_type == 'daily' ? $request->daily_start_time : null,
                'daily_end_time' => $request->event_type == 'daily' ? $request->daily_end_time : null,
                'background_color' => $request->background_color,
                'text_color' => $request->text_color,
                'show_on_frontend' => $request->show_on_frontend ?? 1,
                'status' => $request->status,
                'is_active' => $request->status == 'active' ? 1 : 0,
                'created_by' => authId(),
            ]);

            // Add products to event if provided
            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $productData) {
                    if (isset($productData['product_id'])) {
                        $event->addProduct(
                            $productData['product_id'],
                            $productData['event_price'] ?? null,
                            $productData['discount_amount'] ?? 0,
                            $productData['discount_type'] ?? 'flat',
                            $productData['product_priority'] ?? 0
                        );
                    }
                }
                $event->updateTotalProducts();
            }

            DB::commit();
            Toastr::success(__('Event created successfully'));
            return redirect()->route('events');

        } catch (\Exception $e) {
            DB::rollBack();
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

        DB::beginTransaction();
        try {
            $event = Event::findOrFail($id);

            $request->validate([
                'event_title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:events,slug,' . $id,
                'description' => 'nullable|string',
                'event_priority' => 'required|integer|min:0',
                'event_type' => 'required|in:date_range,daily,recurring',
                'event_schedule_start' => 'required_if:event_type,date_range|nullable|date',
                'event_schedule_end' => 'required_if:event_type,date_range|nullable|date|after:event_schedule_start',
                'daily_start_time' => 'required_if:event_type,daily|nullable|date_format:H:i',
                'daily_end_time' => 'required_if:event_type,daily|nullable|date_format:H:i|after:daily_start_time',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'required|in:draft,active,paused,expired,cancelled',
            ]);

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

            $event->update([
                'event_title' => $request->event_title,
                'slug' => $request->slug,
                'description' => $request->description,
                'banner_image' => $bannerImage,
                'banner_image_id' => $request->banner_image_id ?: $event->banner_image_id,
                'event_priority' => $request->event_priority,
                'event_type' => $request->event_type,
                'event_schedule_start' => $request->event_type == 'date_range' ? $request->event_schedule_start : null,
                'event_schedule_end' => $request->event_type == 'date_range' ? $request->event_schedule_end : null,
                'daily_start_time' => $request->event_type == 'daily' ? $request->daily_start_time : null,
                'daily_end_time' => $request->event_type == 'daily' ? $request->daily_end_time : null,
                'background_color' => $request->background_color,
                'text_color' => $request->text_color,
                'show_on_frontend' => $request->show_on_frontend ?? 1,
                'status' => $request->status,
                'is_active' => $request->status == 'active' ? 1 : 0,
                'updated_by' => authId(),
            ]);

            // Update products in event
            if ($request->has('products') && is_array($request->products)) {
                // Remove existing products not in the new list
                $newProductIds = collect($request->products)->pluck('product_id')->filter()->toArray();
                $event->eventProducts()->whereNotIn('product_id', $newProductIds)->delete();

                // Add or update products
                foreach ($request->products as $productData) {
                    if (isset($productData['product_id'])) {
                        $event->addProduct(
                            $productData['product_id'],
                            $productData['event_price'] ?? null,
                            $productData['discount_amount'] ?? 0,
                            $productData['discount_type'] ?? 'flat',
                            $productData['product_priority'] ?? 0
                        );
                    }
                }
                $event->updateTotalProducts();
            }

            DB::commit();
            Toastr::success(__('Event updated successfully'));
            return redirect()->route('events');

        } catch (\Exception $e) {
            DB::rollBack();
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
            $event->update([
                'status' => $event->status == 'active' ? 'paused' : 'active',
                'is_active' => $event->status == 'active' ? 0 : 1,
                'updated_by' => authId(),
            ]);

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

            DB::commit();

            $response['message'] = __('Product added to event successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            $response['event_product'] = $eventProduct->load('product');
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
     * Remove product from event
     */
    public function removeProduct(Request $request, $eventId)
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
            ]);

            $event = Event::findOrFail($eventId);
            $event->removeProduct($request->product_id);
            $event->updateTotalProducts();

            DB::commit();

            $response['message'] = __('Product removed from event successfully');
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

            $eventProduct->update([
                'event_price' => $request->event_price,
                'discount_amount' => $request->discount_amount,
                'discount_type' => $request->discount_type,
                'product_priority' => $request->product_priority,
                'event_stock' => $request->event_stock,
                'is_active' => $request->is_active,
                'status' => $request->is_active ? 'active' : 'paused',
                'badge_text' => $request->badge_text,
                'badge_color' => $request->badge_color,
                'updated_by' => authId(),
            ]);

            DB::commit();

            $response['message'] = __('Event product updated successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            $response['event_product'] = $eventProduct->load('product');
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