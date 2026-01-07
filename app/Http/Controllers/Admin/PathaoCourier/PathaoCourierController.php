<?php

namespace App\Http\Controllers\Admin\PathaoCourier;
use Illuminate\Http\Request;
use App\Services\PathaoService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Admin\PathaoCourierRequest;
use App\Http\Requests\Admin\PathaoWebhookRequest;
use App\Http\Requests\Admin\PathaoCourierZoneRequest;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\Admin\PathaoCourier\PathaoCourierInterface;

class PathaoCourierController extends Controller
{
    private $pathao;
    protected $order;

    public function __construct(OrderInterface $order, PathaoCourierInterface $pathao)
    {
        $this->pathao       = $pathao;
        $this->order    = $order;
    }
    
    public function index(Request $request)
    {
        try {
            // Validate the incoming orderId
            $request->validate([
                'orderId' => 'required|numeric|min:1|exists:orders,id'
            ]);
        } catch (ValidationException $e) {
            // If validation fails, redirect the user with error messages
            return redirect()->route('orders');
        }

        $order = $this->order->get($request['orderId']);

        return view('admin.PathaoCourier.index', ['orderData'=> $order,'orderID' => $request['orderId']]);
    }

    public function store(PathaoCourierRequest $request, PathaoService $pathaoService)
    {
        try {
            // Fetch delivery data from the Pathao service
            $deliveryData = $pathaoService->order($request);

            // Retrieve order data using the provided order ID
            $orderData = $this->order->get($request->orderId);

            // Process the order and delivery data
            return $this->pathao->processOrder($orderData, $deliveryData);

        } catch (\Exception $e) {

            // Check if the exception message is in JSON format
            $errorMessage = json_decode($e->getMessage(), true);

            if (is_array($errorMessage)) {
                // Return the formatted error response
                return response()->json($errorMessage, $errorMessage['code']);
            }

            // Handle unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'type' => 'error',
                'code' => 500,
                'errors' => [],
            ], 500);
        }
    }

    public function city(PathaoService $pathaoService)
    {
        return $pathaoService->getCity();
    }

    public function zone(Request $request, PathaoService $pathaoService)
    {
        $rules = [
            'city_id' => 'required|numeric|min:1'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return $pathaoService->getZone($request);
    }

    public function updateStatus(PathaoWebhookRequest $request)
    {
        $this->pathao->updateStatus($request);
    }

}
