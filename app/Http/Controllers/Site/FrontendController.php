<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource\BrandPaginateResource;
use App\Http\Resources\SiteResource\CampaignPaginateResource;
use App\Http\Resources\SiteResource\CategoryResource;
use App\Http\Resources\SiteResource\ContactResource;
use App\Http\Resources\SiteResource\ProductPaginateResource;
use App\Http\Resources\SiteResource\ShopPaginateResource;
use App\Http\Resources\SiteResource\VideoPaginateResource;
use App\Http\Resources\SiteResource\WishlistResource;
use App\Models\Event;
use App\Repositories\Admin\Page\PageRepository;
use App\Repositories\Interfaces\Admin\Addon\VideoShoppingInterface;
use App\Repositories\Interfaces\Admin\Blog\BlogInterface;
use App\Repositories\Interfaces\Admin\CurrencyInterface;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\Marketing\CampaignInterface;
use App\Repositories\Interfaces\Admin\Marketing\SubscriberInterface;
use App\Repositories\Interfaces\Admin\MediaInterface;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\Admin\Product\BrandInterface;
use App\Repositories\Interfaces\Admin\Product\CategoryInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Repositories\Interfaces\Site\AddressInterface;
use App\Repositories\Interfaces\Site\CartInterface;
use App\Repositories\Interfaces\Site\ContactUsInterface;
use App\Repositories\Interfaces\Site\ReviewInterface;
use App\Repositories\Interfaces\Site\WishlistInterface;
use App\Services\CampaignPricingService;
use App\Traits\HomePage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FrontendController extends Controller
{
    use HomePage;

    protected $product;
    protected $review;
    protected $blog;

    public function __construct(ProductInterface $product, ReviewInterface $review, BlogInterface $blog)
    {
        $this->product = $product;
        $this->review = $review;
        $this->blog = $blog;
    }

    public function home(MediaInterface $media, CategoryInterface $category, SellerInterface $seller,ProductInterface $product, BrandInterface $brand, CampaignInterface $campaign,VideoShoppingInterface $shopping,Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $data           = $this->parseSettingsData($media, $category, $seller, $brand, $campaign,$shopping,$request->page,$product);

            return response()->json([
                'components'        => $data['components'],
                'component_names'   => $data['component_names'],
                'has_more_data' => !(count(settingHelper('home_page_contents')) < $request->page * 3),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function contactPage(PageRepository $pageRepository): \Illuminate\Http\JsonResponse
    {

        try {
            $data = [
                'contact'       =>new ContactResource($pageRepository->contactPage()),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function page(Request $request,PageRepository $pageRepository): \Illuminate\Http\JsonResponse
    {
        try {
            $page = $pageRepository->pageBySlug($request->slug);
            $data = [
                'page' => [
                    'title'     => $page->getTranslation('title',languageCheck()),
                    'link'      => $page->link,
                    'content'   => $page->getTranslation('content',languageCheck())
                ]
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function campaigns(CampaignInterface $campaign): \Illuminate\Http\JsonResponse
    {
        try {
            // Get active and upcoming events (campaigns) using the Event system
            $now = Carbon::now()->format('Y-m-d H:i:s');

            $events = Event::where('show_on_frontend', 1)
                ->where(function ($q) use ($now) {
                    $q->where('status', 'active')
                        ->where('is_active', 1)
                        ->where(function ($q2) use ($now) {
                            $q2->where('event_type', 'daily')
                                ->orWhere(function ($q3) use ($now) {
                                    $q3->where('event_type', 'date_range')
                                        ->where('event_schedule_start', '<=', $now)
                                        ->where('event_schedule_end', '>=', $now);
                                });
                        });
                })
                ->orWhere(function ($q) use ($now) {
                    // Include upcoming campaigns (status = active but not yet started)
                    $q->where('status', 'active')
                        ->where('event_type', 'date_range')
                        ->where('event_schedule_start', '>', $now);
                })
                ->orderBy('event_priority', 'asc')
                ->orderBy('event_schedule_start', 'asc')
                ->paginate(12);

            // Transform events to campaign format for frontend compatibility
            $campaigns = $events->getCollection()->map(function ($event) use ($now) {
                $isActive = ($event->status == 'active' && $event->is_active == 1 && $event->is_active_now);
                $isUpcoming = ($event->status == 'active' && $event->event_type == 'date_range' && $event->event_schedule_start > $now);

                return [
                    'id' => $event->id,
                    'title' => $event->event_title,
                    'slug' => $event->slug,
                    'short_description' => $event->description,
                    'description' => $event->description,
                    'image_374x374' => $event->image_374x374,
                    'image_1920x412' => $event->image_1920x412,
                    'banner' => $event->image_1920x412,
                    'campaign_start_date' => $event->event_schedule_start,
                    'campaign_end_date' => $event->event_schedule_end,
                    'event_schedule_start' => $event->event_schedule_start,
                    'event_schedule_end' => $event->event_schedule_end,
                    'is_active_now' => $event->is_active_now,
                    'campaign_type' => $event->campaign_type ?? 'product',
                    'badge_text' => $event->badge_text,
                    'badge_color' => $event->badge_color,
                    'status' => $event->status,
                    'is_active' => $isActive,
                    'is_upcoming' => $isUpcoming,
                ];
            });

            $events->setCollection($campaigns);

            $data = [
                'campaigns' => $events
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get active event/campaign for header menu display
     * Returns only currently running events with time-based validation
     */
    public function activeEvent(): \Illuminate\Http\JsonResponse
    {
        try {
            $activeEvent = null;

            // Use CampaignPricingService to get the currently active event
            if (class_exists(CampaignPricingService::class)) {
                $pricingService = app(CampaignPricingService::class);
                $activeCampaign = $pricingService->getActiveCampaign();

                if ($activeCampaign) {
                    $activeEvent = [
                        'id' => $activeCampaign->id,
                        'title' => $activeCampaign->event_title,
                        'slug' => $activeCampaign->slug,
                        'badge_text' => $activeCampaign->badge_text,
                        'badge_color' => $activeCampaign->badge_color,
                        'background_color' => $activeCampaign->background_color,
                        'text_color' => $activeCampaign->text_color,
                        'event_type' => $activeCampaign->event_type,
                        'is_active_now' => $activeCampaign->is_active_now,
                    ];
                }
            }

            return response()->json([
                'active_event' => $activeEvent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'active_event' => null,
                'error' => null // Don't show error to frontend
            ]);
        }
    }

    public function categories(CategoryInterface $category): \Illuminate\Http\JsonResponse
    {

        try {
            $data = [
                'categories' => CategoryResource::collection($category->categoryPage())
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function allActiveCategories(CategoryInterface $category): \Illuminate\Http\JsonResponse
    {

        try {
            $data = [
                'categories' => CategoryResource::collection($category->allActiveCategories())
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function dailyDeals(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'products' => new ProductPaginateResource($this->product->dailyDeals($request->paginate))
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function giftIdea(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'products' => new ProductPaginateResource($this->product->giftIdea($request->paginate))
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function businessIdea(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'products' => new ProductPaginateResource($this->product->businessIdea($request->paginate))
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cartPage(CartInterface $cart): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'carts' => $cart->userCart(),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function checkout(AddressInterface $address): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'addresses' => $address->userAddress(),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function trackOrder(Request $request, OrderInterface $order): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'order' => $order->orderByCode($request->all())
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function brands(BrandInterface $brand): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'brands' => new BrandPaginateResource($brand->allBrands())
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sellers(SellerInterface $seller,Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'sellers' => settingHelper('seller_system') == 1 ? new ShopPaginateResource($seller->allSeller($request->all())) : []
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function contactUs(ContactUsInterface $contactUs, Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
        try {
            $data = [
                'contact' => $contactUs->storeContact($request),
                'success' => __('Message Sent Successfully'),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function reply(ContactUsInterface $contactUs, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'reply' => $contactUs->reply($request),
                'success' => __('Reply Sent Successfully'),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function changeLocale(LanguageInterface $language, $locale): \Illuminate\Http\JsonResponse
    {
        try {
            session()->put('lang', $locale);
            $language = $language->getByLocale($locale);

            if (authUser() && $language)
            {
                authUser()->update([
                    'lang_code' => $locale
                ]);
            }

            $data   = [
                'active_language'   => $language,
            ];

            session()->put('text_direction',$language->text_direction);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' =>$e->getMessage()
            ]);
        }

    }

    public function langKeywords(LanguageInterface $language): \Illuminate\Http\JsonResponse
    {
        try {
            $lang = languageCheck();

            $data = [
                'lang'      => file_exists(base_path('resources/lang/' . $lang . '.json')) ?  $lang  : 'en',
                'language'  => $language->getByLocale($lang),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function changeCurrency(CurrencyInterface $currency, $code): \Illuminate\Http\JsonResponse
    {
        try {
            $currency = $currency->currencyByCode($code);

            if (authUser() && $currency)
            {
                authUser()->update([
                    'currency_code' => $currency->currency_code
                ]);
            }

            if ($currency && ($code == 'USD' || settingHelper('live_api_currency') != 1)) {
                session()->put('currency', $currency->id);
                return response()->json([
                    'active_currency' => $currency,
                    'success' => __('Currency Changed Successfully'),
                ]);
            }
            if ($currency) {
                session()->put('currency', $currency->id);
                $fields = [
                    'access_key' => settingHelper('live_currency_access_key'),
                    'from' => 'USD',
                    'to' => $code,
                    'amount' => 1,
                ];

                $response = httpRequest("http://api.exchangeratesapi.io/v1/convert",$fields,[],false,'GET');

                if (arrayCheck('result', $response))
                {
                    $rate = $response['result'];
                    if ($rate != $currency->exchange_rate) {
                        $currency->exchange_rate = $rate;
                        $currency->save();
                        cache()->flush();
                        Artisan::call('optimize:clear');
                    }
                    return response()->json([
                        'success' => __('currency_rate_updated'),
                        'active_currency' => $currency
                    ]);
                }
                else{
                    return response()->json([
                        'error' => __('Oops...Something Went Wrong')
                    ]);
                }
            } else {
                return response()->json([
                    'active_currency'   => [
                        'exchange_rate' => 1,
                        'name'          => 'USD',
                        'symbol'        => '$',
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function wishlist(WishlistInterface $wislist): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'wishlist' => new WishlistResource($wislist->userWishlist(10)),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function subscribers(Request $request, SubscriberInterface $subscriber): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'email' => 'required|email|unique:subscribers',
        ]);
        try {
            $data = [
                'subscribe' => $subscriber->store($request->email),
                'success' => __('You have subscribed successfully'),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function videoShopping(VideoShoppingInterface $shopping): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'videos' => new VideoPaginateResource($shopping->all()->active()->SellerCheck()->paginate(12)),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function videoShoppingDetails(VideoShoppingInterface $shopping,$slug): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'video' => $shopping->shopBySlug($slug),
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
