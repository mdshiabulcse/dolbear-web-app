<template>
  <div class="container cart-new">
      <h4 class="mt-3">SHOPPING CART</h4>
      <div class="row">

          <div class="col-lg-8">
            <table class="cart-table" >
              <thead >
              <tr>
                <th class="hide-mobile">Sl.</th>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(cart, index) in carts" :key="cart.id" >
                <td class="hide-mobile">{{ index + 1 }}</td>
                <td class="d-flex flex-column align-items-start">
                  <span>{{ cart.product_name }} <span v-if="cart.variant">({{ cart.variant }})</span> </span>
                </td>
                <td>
                  <div v-if="cart.discount > 0 || cart.coupon_discount > 0">
                    <del style="color: #999;">{{ priceFormat(cart.original_price || cart.price + cart.discount) }}</del>
                    <span style="color: #ff6b6b; font-weight: 600; margin-left: 5px;">{{ priceFormat(cart.price) }}</span>
                    <div v-if="cart.coupon_discount > 0" style="font-size: 11px; color: #28a745;">
                      Coupon: -{{ priceFormat(cart.coupon_discount) }}
                    </div>
                  </div>
                  <span v-else>{{ priceFormat(cart.price) }}</span>
                </td>
                <td>
                  <div class="product-quantity">
                    <div class="quantity" data-trigger="spinner">
                      <a class="btn pull-left" @click="cartMinus(index)"
                         href="javascript:void(0);"
                         data-spin="down">
                        <span class="mdi mdi-name mdi-minus"></span>
                      </a>
                      <input type="text" name="quantity"
                             v-model="payment_form.quantity[index].quantity"
                             title="quantity" readonly
                             class="input-text">
                      <a class="btn pull-right" @click="cartPlus(index)"
                         href="javascript:void(0);" data-spin="up">
                        <span class="mdi mdi-name mdi-plus"></span>
                      </a>
                    </div>
                  </div>
                </td>
                <td>{{ priceFormat(cart.price * payment_form.quantity[index].quantity) }}</td>
                <td>
                  <img :src="actionIcon" style="cursor: pointer;" alt="Delete" @click="deleteCart(cart.id)" />
                </td>
              </tr>
              </tbody>
            </table>
            <div class="row">
                  <h4 class="mb-3">Delivery Address</h4>
                  <div class="col-lg-6">
                      <p>Full Name <span class="text-danger">*</span></p>
                      <input
                        type="text"
                        class="form-control mb-1 mb-md-3"
                        placeholder="Full Name"
                        v-model="form.name" required
                        >
                  </div>
                  <div class="col-lg-6">
                      <p>Phone Number <span class="text-danger">*</span></p>
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Phone Number"
                        v-model="form.phone_no"
                        @input="sanitizePhoneNumber"
                         required
                      >
                  </div>
                  <div class="col-lg-6">
                      <p>Email <span class="text-danger">*</span></p>
                      <input
                        type="email"
                        class="form-control"
                        placeholder="Email"
                        v-model="form.email"
                        required
                      >
                  </div>

                <div class="col-lg-6">
                  <div>
                    <p>Division  <span class="text-danger">*</span></p>
                    <select
                      class="form-control"
                      v-model="form.division_id"
                      @change="getStates()"
                      :class="{ 'error_border' : errors.division_id }"
                    >
                      <option value="">Select a Division</option>
                      <option v-for="division in divisions" :key="division.id" :value="division.id">
                        {{ division.name }}
                      </option>
                    </select>
                  </div>
                  <span class="validation_error" v-if="errors.division_id">{{ errors.division_id[0] }}</span>
                </div>

                <div class="col-md-6">
                  <div>
                    <p>District  <span class="text-danger">*</span></p>
                    <select
                      class="form-control"
                      v-model="form.district_id"
                      @change="getCities()"
                      :class="{ 'error_border' : errors.district_id }"
                      :disabled="!form.division_id"
                    >
                      <option value="">Select a District</option>
                      <option v-for="state in states" :key="state.id" :value="state.id">
                        {{ state.name }}
                      </option>
                    </select>
                  </div>
                  <span class="validation_error" v-if="errors.district_id">{{ errors.district_id[0] }}</span>
                </div>

                <div class="col-md-6">
                  <div>
                    <p>Thana  <span class="text-danger">*</span></p>
                    <select
                      class="form-control"
                      v-model="form.thana_id"
                      @change="getDeliveryCharge()"
                      :class="{ 'error_border' : errors.thana_id }"
                      :disabled="!form.district_id"
                    >
                      <option value="">Select a Thana</option>
                      <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                      </option>
                    </select>
                  </div>
                  <span class="validation_error" v-if="errors.thana_id">{{ errors.thana_id[0] }}</span>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <div class="form-group">
                      <label>Address</label>
                      <textarea v-model="form.address"
                      placeholder="Write your full address"
                                class="form-control"
                                :class="{ 'error_border' : errors.address }"
                                ></textarea>
                    </div>
                  </div>
                  <span class="validation_error"
                        v-if="errors.address">{{ errors.address[0] }}</span>
                </div>
              </div>

          </div>

          <div class="col-lg-4">
              <div class="row mb-3">
                  <p class="col-lg-3 mb-3 mb-md-0">Delivery :</p>
                  <div class="col-lg-9 d-flex flex-wrap gap-2">
                      <div v-for="(option, index) in deliveryOptions" :key="index" class="delivery-card"
                          :class="{ active: deliveryMethod === option, disabled: isExpressDeliveryDisabled && option === EXPRESS_DELIVERY }"
                          @click="!isExpressDeliveryDisabled || option !== EXPRESS_DELIVERY ? setDeliveryActiveOption(index) : null">
                          <p @change="getDeliveryCharge">{{ option }}</p>
                      </div>
                  </div>
                  <div v-if="isExpressDeliveryDisabled && deliveryMethod !== EXPRESS_DELIVERY" class="col-12">
                      <small class="text-muted">Express delivery is only available in Dhaka district </small>
                  </div>
              </div>

            <div class="checkout-card mb-3" v-if="deliveryMethod === STORE_PICK">
              <h4>Select Store</h4>
              <div class="payment-method">
                <div v-for="(store, index) in filteredStoreOptions" :key="index"
                     class="store-selection-item"
                     :class="{ active: selectedStore === store.id }"
                     @click="setActiveStore(store.id)">
                  <p class="store-name">{{ store.name }}</p>
                  <div v-if="selectedStore === store.id" class="store-details-info">
                    <p class="store-address"><strong>Address:</strong> {{ store.address || 'N/A' }}</p>
                    <p class="store-phone"><strong>Contact:</strong> {{ store.phone || 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </div>

              <div class="checkout-card mb-3">
                  <h4>Select Payment Method</h4>
                  <div class="payment-method">
                      <p v-for="(method, index) in paymentOptions" :key="index"
                          :class="{ active: payment_method === method.value }" @click="setActivePaymentOption(index)">{{
                              method.name }}</p>
                  </div>
              </div>

              <div class="checkout-card">
                  <h4>Checkout Summary</h4>
                  <div class="d-flex justify-content-between">
                      <p>Subtotal</p>
                      <p>৳ {{ payment_form.sub_total }}</p>
                  </div>

                  <div class="d-flex justify-content-between">
                      <p>Delivery Charge</p>
                      <p v-if="free_shipping && deliveryMethod === STANDARD">Free</p>
                      <p v-else-if="payment_form.shipping_tax == 0">Free</p>
                      <p v-else>৳ {{ payment_form.shipping_tax }}</p>
                  </div>
                  <div class="d-flex justify-content-between">
                      <p>Discount </p>
                      <p>৳ {{ payment_form.discount_offer }}</p>
                  </div>
                <div class="d-flex justify-content-between">
                      <p>Coupon Discount </p>
                      <p>৳ {{ parseFloat(payment_form.coupon_discount).toFixed(2) }}</p>
                  </div>

                  <!-- Applied Coupons List with Delete Button -->
                  <div v-if="coupon_list && coupon_list.length > 0" class="applied-coupons mb-3">
                      <div v-for="(coupon, index) in coupon_list" :key="index"
                           class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                          <div class="w-100">
                              <div class="d-flex justify-content-between align-items-center">
                                  <div>
                                      <div class="badge bg-success">{{ coupon.code || 'Applied' }}</div><br>
                                      <small class="ms-2 text-muted">
                                          {{ coupon.discount_type === 'flat' ? 'Flat: ' : coupon.discount_type === 'percent' ? 'Percent: ' : '' }}
                                          {{ coupon.discount_type === 'percent' ? coupon.coupon_discount + '%' : priceFormat(coupon.coupon_discount) }}
                                      </small>
                                      <small class="ms-2 text-success">
                                          (Discount: ৳ {{ parseFloat(payment_form.coupon_discount).toFixed(2) }})
                                      </small>
                                      <div v-if="coupon.applicable_on_discount == 0" class="ms-2 mt-1">
                                          <small class="text-info">* Applicable only to non-discounted products</small>
                                      </div>
                                  </div>
                                  <img
                                      @click="removeCoupon(coupon.coupon_id)"
                                      src="/images/img/icon/deletecart.png"
                                      alt="Remove"
                                      style="cursor: pointer; width: 20px; height: 20px; opacity: 0.7;"
                                      :style="{ opacity: disabled ? 0.3 : 0.7, cursor: disabled ? 'not-allowed' : 'pointer' }"
                                      title="Remove Coupon"
                                  />
                              </div>
                          </div>
                      </div>
                  </div>

                  <form @submit.prevent="applyCoupon">
                    <div class="d-flex justify-content-center">

                        <input
                            type="text"
                            class="promo-input"
                            v-model="payment_form.coupon_code"
                            placeholder="Coupon Code"
                            :disabled="!carts || carts.length === 0"
                            :class="{ 'disabled': !carts || carts.length === 0 }"
                        />

                        <!-- <loading_button
                            v-if="loading"
                            :class="promo-btn">
                        </loading_button> -->
                        <button
                          class="promo-btn"
                          :disabled="!carts || carts.length === 0"
                          :class="{ 'disabled': !carts || carts.length === 0 }"
                        >Promo</button>
                    </div>
                  </form>

                  <span></span>
                  <div class="d-flex justify-content-between">
                      <h4>Total Payable</h4>
                      <h4>৳ {{ parseFloat(payment_form.total).toFixed(2) }}</h4>
                  </div>
                  <div class="d-flex flex-column justify-content-center">

                      <div class="d-flex " style="position: relative;">
                          <div class="" style="position: absolute; top: 2px; margin-right: 10px;">
                            <input id="terms_condition" v-model="terms_condition" type="checkbox" >
                          </div>

                          <label for="terms_condition" class=" text-muted mb-4 ms-4 ">I agree to the <a href="/page/terms-conditions" class="link_section"> terms and condition </a> , <a href="/page/privacy-policy" class="link_section"> privacy policy </a>  and <a href="/page/refund-policy" class="link_section"> refund policy </a> </label>

                      </div>
                      <button
                        class="checkout-btn"
                        @click="confirmOrder"
                        :disabled="loading"
                      >
                        <div v-if="loading">
                          <i class="fa fa-spinner fa-spin"></i> Processing...
                        </div>
                        <div v-else>Confirm Order</div>
                      </button>
                  </div>

              </div>
          </div>
      </div>

  </div>

</template>

<script>
import {EXPRESS_DELIVERY, STANDARD, STORE_PICK} from "../../../constants/deliveryMethod";

// Analytics Tracking Helper for Cart Page
const Analytics = {
    isGTMReady() {
        return typeof window.dataLayer !== 'undefined';
    },
    isFacebookReady() {
        return typeof window.fbq !== 'undefined';
    },
    getCurrencyCode(activeCurrency) {
        // Default to BDT for Dolbear Bangladesh site
        return (activeCurrency && activeCurrency.code) ? activeCurrency.code : 'BDT';
    },
    trackViewCart(carts, activeCurrency) {
        if (!this.isGTMReady() || !carts || !Array.isArray(carts) || carts.length === 0) {
            return;
        }
        try {
            const currency = this.getCurrencyCode(activeCurrency);
            // cart.price is already the active price (campaign or discounted or original)
            const totalValue = carts.reduce((sum, item) => {
                return sum + (parseFloat(item.price) * parseInt(item.quantity));
            }, 0);

            const items = carts.map(cart => ({
                item_id: cart.sku || String(cart.product_id),
                item_name: cart.product_name || '',
                item_variant: cart.variant || '',
                item_category: cart.category_name || '',
                price: parseFloat(cart.price),  // Already the active price
                quantity: parseInt(cart.quantity)
            }));

            // GA4 - View Cart
            window.dataLayer.push({
                event: 'view_cart',
                ecommerce: {
                    currency: currency,
                    value: totalValue,
                    items: items
                }
            });

            // NOTE: Facebook Pixel tracking removed to prevent duplicates
            // GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events

            // Analytics: View cart tracked
        } catch (error) {
            console.error('[Analytics] Error tracking view cart:', error);
        }
    },
    trackAddToCart(product, quantity, activeCurrency) {
        if (!this.isGTMReady() || !product) {
            return;
        }
        try {
            const currency = this.getCurrencyCode(activeCurrency);
            // product.price is already the active price (campaign or discounted or original)
            const price = parseFloat(product.price);
            const value = price * parseInt(quantity);

            // GA4 - Add to Cart
            window.dataLayer.push({
                event: 'add_to_cart',
                ecommerce: {
                    currency: currency,
                    value: value,
                    items: [{
                        item_id: product.sku || String(product.product_id),
                        item_name: product.product_name || '',
                        item_variant: product.variant || '',
                        item_category: product.category_name || '',
                        price: price,  // Already the active price
                        quantity: parseInt(quantity)
                    }]
                }
            });

            // NOTE: Facebook Pixel tracking removed to prevent duplicates
            // GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events

            // Analytics: Add to cart tracked
        } catch (error) {
            console.error('[Analytics] Error tracking add to cart:', error);
        }
    },
    trackRemoveFromCart(product, quantity, activeCurrency) {
        if (!this.isGTMReady() || !product) {
            return;
        }
        try {
            const currency = this.getCurrencyCode(activeCurrency);
            // product.price is already the active price (campaign or discounted or original)
            const price = parseFloat(product.price);
            const value = price * parseInt(quantity);

            // GA4 - Remove from Cart
            window.dataLayer.push({
                event: 'remove_from_cart',
                ecommerce: {
                    currency: currency,
                    value: value,
                    items: [{
                        item_id: product.sku || String(product.product_id),
                        item_name: product.product_name || '',
                        item_variant: product.variant || '',
                        item_category: product.category_name || '',
                        price: price,  // Already the active price
                        quantity: parseInt(quantity)
                    }]
                }
            });

            // Analytics: Remove from cart tracked
        } catch (error) {
            console.error('[Analytics] Error tracking remove from cart:', error);
        }
    },
    trackBeginCheckout(carts, activeCurrency) {
        if (!this.isGTMReady() || !carts || !Array.isArray(carts) || carts.length === 0) {
            return;
        }
        try {
            const currency = this.getCurrencyCode(activeCurrency);
            // cart.price is already the active price (campaign or discounted or original)
            const totalValue = carts.reduce((sum, item) => {
                return sum + (parseFloat(item.price) * parseInt(item.quantity));
            }, 0);

            const items = carts.map(cart => ({
                item_id: cart.sku || String(cart.product_id),
                item_name: cart.product_name || '',
                item_variant: cart.variant || '',
                item_category: cart.category_name || '',
                price: parseFloat(cart.price),  // Already the active price
                quantity: parseInt(cart.quantity)
            }));

            // GA4 - Begin Checkout
            window.dataLayer.push({
                event: 'begin_checkout',
                ecommerce: {
                    currency: currency,
                    value: totalValue,
                    items: items
                }
            });

            // NOTE: Facebook Pixel tracking removed to prevent duplicates
            // GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events

            // Analytics: Begin checkout tracked
        } catch (error) {
            console.error('[Analytics] Error tracking begin checkout:', error);
        }
    }
};

export default {
  async mounted() {

    if(!this.checkout())
    {
      this.$router.push({name: 'login'});
    }

    if(!this.cartList)
    {
      this.$router.push({name: 'home'});
    }

    setTimeout(() => {
      document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
        backdrop.remove();
      });
    }, 300);

    await this.getDivisions()
    this.getCheckout()
    this.getStore()
    await this.getUserAddress()

    // Analytics: Track cart view on page load
    this.$nextTick(() => {
      this.trackCartView();
    });
  },
  watch: {
    cartList: {
      handler(newValue, oldValue) {
        // CRITICAL FIX: Only reload if cart content actually changed
        // Check if length changed or if we're coming from another page (home page add to cart)
        const lengthChanged = (!oldValue && newValue) || (oldValue && newValue && oldValue.length !== newValue.length);
        const comingFromOutside = !this.isInternalUpdate;

        if (lengthChanged || comingFromOutside) {
          // Cart list changed - reloading from backend
          this.getCheckout();
        }

        // Automatically remove all coupons if cart becomes empty
        if (!newValue || newValue.length === 0) {
          this.removeAllCoupons();
        }

        // Reset the flag after handling
        this.$nextTick(() => {
          this.isInternalUpdate = false;
        });
      },
      deep: true
    },
  },
  computed: {
    cartList() {
      return this.$store.getters.getCarts;
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    countries() {
      return this.$store.getters.getCountryList;
    },
    filteredStoreOptions() {
      // Filter out "Dolbear Online Store" from the store list
      if (!this.storeOption) return [];
      return this.storeOption.filter(store => {
        return store.name && store.name.toLowerCase() !== 'dolbear online';
      });
    },
    isExpressDeliveryDisabled() {
      // Check if express delivery should be disabled based on selected district
      if (!this.form.district_id || !this.states) {
        return false;
      }
      const selectedState = this.states.find(s => s.id === this.form.district_id);
      if (!selectedState) {
        return false;
      }
      // Disable express delivery if not in Dhaka district
      const isDhaka = selectedState.name.toLowerCase().includes('dhaka') ||
                     (selectedState.id === 3045); // Adjust ID as needed
      return !isDhaka;
    },
  },
  data() {
      return {
        STANDARD,
        EXPRESS_DELIVERY,
        STORE_PICK,
        actionIcon: "/images/img/icon/deletecart.png",
        deliveryOptions: [STANDARD, EXPRESS_DELIVERY, STORE_PICK],
        paymentOptions: [
            {
              name:'Cash on Delivery',
              value:'cash_on_delivery'
            },
          {
            name:'Online Payment',
            value:'online_payment'
          },
        ],

        free_shipping: false,
        storeOption: null,

        code:
            typeof this.$route.params.code != "undefined"
                ? this.$route.params.code
                : "",

        trx_id: '',
        terms_condition: false,

        carts: [],
        seller_carts: [],
        coupon_area: true,
        coupon: [],
        cart_length: 0,
        collapseAttribute: [],
        disable: false,
        disabled: false,
        is_shimmer: false,
        coupon_list: [],
        shipping_classes: [],
        isInternalUpdate: false,  // Flag to prevent infinite reload loops

        loading: false,

        //address
        form: {
          name: '',
          phone_no: '',
          email: '',
          alternative_phone_no: '',
          address: '',
          country_id: '',
          division_id: '',
          district_id: '',
          thana_id: '',
          id: '',
          delivery_charge: 0,
        },
        deliveryMethod: STANDARD,
        payment_method: 'cash_on_delivery',
        selectedStore: '',

        divisions: [],
        states: [],
        cities: [],
        address_submit_loading: false,
        cartViewTracked: false,
      };
  },
  methods: {
    async takeOrders() {
      let carts = this.cartList;
      let url = this.getUrl("user/payment-order?code=" + this.code);

      try {
        const response = await axios.get(url); // Use await here
      } catch (error) {
        console.error("Error in takeOrders:", error);
        this.$Progress.fail();
        toastr.error(this.lang.something_went_wrong, this.lang.Error + " !!");
      }
    },

    getDivisions() {
      let url = this.getUrl('get/division-list/');
      // Fetching divisions
      axios.get(url).then((response) => {
        // Divisions loaded
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.divisions = response.data.divisions;
          // Divisions stored
        }
      }).catch((error) => {
        console.error('Error fetching divisions:', error);
        toastr.error('Failed to load divisions', this.lang.Error + ' !!');
      });
    },
    async getUserAddress()
    {
      let url = this.getUrl("user/delivery-address");
      axios.get(url).then(async (response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          const address = response.data
          this.form.name = address.name
          this.form.email = address.email
          this.form.address = address.address
          this.form.phone_no = address.phone_no

          if (address.address_ids?.division_id) {
            this.form.division_id = parseInt(address.address_ids?.division_id)
            await this.getStates()
          }

          if (address.address_ids?.district_id) {
            this.form.district_id = parseInt(address.address_ids?.district_id)
            await this.getCities()
          }

          if(address.address_ids?.thana_id){
            this.form.thana_id = parseInt(address.address_ids?.thana_id)
            this.getDeliveryCharge()
          }
        }
      });
    },
    getStates(address) {
      let division_id = this.form.division_id;

      let url = this.getUrl('state/by-country/' + division_id);
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.states = response.data.states;
          if (address && address.address_ids) {
            this.form.state_id = parseInt(address.address_ids.state_id);
            this.getCities(address);
          }
        }
      });

      // Reset district and thana, and check if delivery method needs to be reset
      const previousDistrict = this.form.district_id;
      this.form.district_id = "";
      this.form.thana_id = "";

      // If express delivery was selected and we're changing district, reset to standard
      if (this.deliveryMethod === EXPRESS_DELIVERY && previousDistrict) {
        this.deliveryMethod = STANDARD;
        // Show notification to user
        toastr.info('Delivery method reset to Standard due to district change', 'Info');
      }
    },
    getCities(address) {
      let state_id = this.form.district_id;

      let url = this.getUrl('city/by-state/' + state_id);
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.cities = response.data.cities;
          if (address && address.address_ids) {
            this.form.city_id = parseInt(address.address_ids.city_id);
          }

          // Check if the new district supports express delivery
          this.checkExpressDeliveryAvailability();
        }
      });

      this.form.thana_id = "";
    },
    getDeliveryCharge(){

      this.shipping_cost = 0
      this.payment_form.shipping_tax = 0

      this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));

      // Calculate delivery charge for area-based shipping
      // Skip calculation for store pickup
      // For express delivery: always calculate charge (even if all products have free shipping)
      // For standard delivery with all free shipping products: charge is 0 (FREE)
      if (this.settings.shipping_fee_type === 'area_base' && this.deliveryMethod !== STORE_PICK) {
        // For express delivery, always calculate charge
        // For standard delivery, only calculate if not all products have free shipping
        const shouldCalculate = this.deliveryMethod === EXPRESS_DELIVERY || !this.free_shipping;

        if (shouldCalculate && this.form?.thana_id) {
          let url = this.getUrl('user/find/shipping_cost');

          let form = {
            city_id: this.form.thana_id,
            deliveryMethod: this.deliveryMethod
          };

          axios.post(url, form).then((response) => {

            if (response.data.error) {
              toastr.error(response.data.error, this.lang.Error + ' !!');
              // Reset to standard delivery if express delivery is not available
              if (this.deliveryMethod === EXPRESS_DELIVERY) {
                this.deliveryMethod = STANDARD;
                // Recalculate with standard delivery
                form.deliveryMethod = STANDARD;
                axios.post(url, form).then((response) => {
                  if (!response.data.error) {
                    this.payment_form.shipping_tax = response.data.shipping_cost;
                    this.shipping_cost = this.payment_form.shipping_tax;
                    this.recalculateTotal();
                  }
                });
              }
            } else {
              this.payment_form.shipping_tax = response.data.shipping_cost;
              this.shipping_cost = this.payment_form.shipping_tax;
              this.recalculateTotal();
            }
          });
        }
      }

    },
    recalculateTotal() {
      // Recalculate total with current shipping charge
      this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
    },
    checkExpressDeliveryAvailability() {
      // Check if express delivery is available for the current district
      if (this.deliveryMethod === EXPRESS_DELIVERY && this.isExpressDeliveryDisabled) {
        // If express delivery is selected but not available, reset to standard
        this.deliveryMethod = STANDARD;
        toastr.info('Express delivery is only available in Dhaka district. Switched to Standard delivery.', 'Info');
        // Recalculate with standard delivery
        this.$nextTick(() => {
          this.getDeliveryCharge();
        });
      } else if (this.deliveryMethod === EXPRESS_DELIVERY && !this.isExpressDeliveryDisabled) {
        // If express delivery is selected and district is Dhaka, recalculate to ensure charge is applied
        this.$nextTick(() => {
          this.getDeliveryCharge();
        });
      }
    },
    setDeliveryActiveOption(index)
    {
        if(this.deliveryOptions[index]){
          this.deliveryMethod = this.deliveryOptions[index];
        }

        // Validate express delivery availability before setting
        if (this.deliveryMethod === EXPRESS_DELIVERY) {
          // Check if selected district is Dhaka
          const selectedState = this.states.find(s => s.id === this.form.district_id);
          if (selectedState) {
            const isDhaka = selectedState.name.toLowerCase().includes('dhaka') ||
                           (selectedState.id === 3045); // Adjust ID as needed

            if (!isDhaka) {
              toastr.error('Express delivery is only available in Dhaka district', this.lang.Error + ' !!');
              this.deliveryMethod = STANDARD;
              return;
            }
          }

          // Check if user has selected a thana
          if (!this.form.thana_id) {
            toastr.warning('Please select a thana first', this.lang.Warning + ' !!');
            this.deliveryMethod = STANDARD;
            return;
          }
        }

        // Recalculate delivery charge when delivery method changes
        this.getDeliveryCharge();
    },
    setActivePaymentOption(index) {
        if(this.paymentOptions[index]){
          this.payment_method = this.paymentOptions[index].value;
        }
    },
    setActiveStore(id) {
      this.selectedStore = id
    },
    getStore(){
      this.$Progress.start();
      let url = this.getUrl('store/allStore');
      axios.get(url).then((response) => {
        this.is_shimmer = true;
        this.$store.commit('setShimmer', 0);
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          this.storeOption = response.data;

          if(response.data){
            this.selectedStore = response.data[0]?.id
          }

        }
      })
    },
    getCheckout() {
      this.$Progress.start();
      let url = this.getUrl('cart/list');
      axios.get(url).then((response) => {
        this.is_shimmer = true;
        this.$store.commit('setShimmer', 0);
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          // CRITICAL FIX: Set flag before updating Vuex to prevent infinite loop
          this.isInternalUpdate = true;

          // Use fresh cart data from backend response
          // This ensures coupon discounts are recalculated based on current cart
          let carts = response.data.carts;
          let checkouts = response.data.checkouts;
          let coupons = response.data.coupons;
          this.free_shipping = response.data.free_shipping;
          this.shipping_classes = response.data.shipping_classes;

          // Update Vuex store with fresh cart data
          this.$store.dispatch('carts', carts);

          // Parse and display fresh data from backend
          this.parseData(carts, checkouts, coupons);
        }
      })
    },
    deleteCart(id) {
      if (confirm("Are you sure?")) {
        // Find the cart item before deleting for tracking
        const cartItem = this.carts.find(c => c.id === id);
        const deletedProductId = cartItem ? cartItem.product_id : null;

        let url = this.getUrl('cart/delete/' + id);
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            // Analytics: Track remove from cart (entire item removed)
            if (cartItem) {
              Analytics.trackRemoveFromCart(cartItem, cartItem.quantity || 1, this.active_currency);
            }

            // CRITICAL FIX: Backend already returns updated data, use it directly
            // Set flag to prevent watcher from triggering
            this.isInternalUpdate = true;
            this.$store.dispatch('carts', response.data.carts);

            // Automatically remove all coupons if cart becomes empty
            if (!response.data.carts || response.data.carts.length === 0) {
              this.removeAllCoupons();
              // Parse data after removing coupons
              this.parseData(response.data.carts, response.data.checkouts, []);
            } else {
              // Validate product-wise coupons after deletion
              // Don't parse yet - validation will handle it
              this.validateProductWiseCoupons(response.data.carts, response.data.coupons, deletedProductId)
                .then(() => {
                  // Validation complete - cart already refreshed by getCheckout()
                })
                .catch(() => {
                  // If validation fails, still parse the original data
                  this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
                });
            }
          }
        })
      }
    },
    checkout() {
      if (!this.authUser) {
        toastr.error('Not Logged In', 'Error' + ' !!');
        this.$store.commit('setLoginRedirection', this.$route.name);
        this.$router.push({name: 'login'});
        return false;
      }
      if (this.authUser.user_type != 'customer') {
        return toastr.warning(this.lang.you_are_not_able_topurchase_products, this.lang.Warning + ' !!');
      }

      return true
    },
    parseData(carts, checkouts, coupons) {
      this.resetForm();
      this.collapseAttribute = [];
      this.carts = [];

      if (carts) {
        for (let i = 0; i < carts.length; i++) {
          this.payment_form.quantity.push({id: carts[i].id, quantity: carts[i].quantity});
          this.carts.push(carts[i]);
          // CRITICAL FIX: Subtotal is always calculated from ORIGINAL price
          // Use original_price if available, otherwise fall back to price
          const originalPrice = carts[i].original_price || carts[i].price;
          this.payment_form.sub_total += parseFloat(originalPrice * carts[i].quantity);
          // discount_offer is the campaign/special discount amount
          this.payment_form.discount_offer += (parseFloat(carts[i].discount) * carts[i].quantity);
          this.payment_form.tax += parseFloat(carts[i].tax * carts[i].quantity);
        }
      }

      this.getDeliveryCharge()

      if (checkouts) {
        this.seller_carts = checkouts;
        this.coupon = checkouts;

        for (let key in this.seller_carts) {
          this.collapseAttribute.push({
            name: checkouts[key].name,
            image: checkouts[key].image,
            status: true,
          });
          if (this.settings.shipping_cost != 'area_base') {
            this.payment_form.shipping_tax += parseFloat(checkouts[key].shipping_cost);
          }
          this.payment_form.tax += parseFloat(checkouts[key].tax);
        }
      }

      if (coupons && this.settings.coupon_system == 1) {
        this.coupon_list = coupons;
        // Store coupon info for recalculation
      }

      // Calculate total coupon discount from cart items (backend-calculated values)
      // The backend sets coupon_discount per cart item based on applicable_on_discount setting
      this.payment_form.coupon_discount = 0;
      if (this.carts && this.carts.length > 0) {
        for (let i = 0; i < this.carts.length; i++) {
          this.payment_form.coupon_discount += parseFloat(this.carts[i].coupon_discount || 0);
        }
      }

      // Recalculate total
      // Formula: (subtotal - discount_offer - coupon_discount) + tax + shipping
      // Where subtotal is based on ORIGINAL prices
      if (this.settings.tax_type == 'after_tax' && this.settings.vat_and_tax_type == 'order_base') {
        this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
        this.payment_form.total += this.payment_form.tax;
        if(this.payment_form.total < 0){
          this.payment_form.total = 0;
        }
      } else {
        this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
        if(this.payment_form.total < 0){
          this.payment_form.total = 0;
        }
      }
    },
    cartPlus(index) {
      if (this.disable) {
        return false;
      }


      if (parseInt(this.payment_form.quantity[index].quantity) <= parseInt(this.carts[index].current_stock)) {
        let formData = {
          id: this.carts[index].id,
          quantity: 1,
        };

        this.disable = true;
        let url = this.getUrl('cart/update');
        axios.post(url, formData).then((response) => {
          this.disable = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            // Analytics: Track add to cart (1 quantity added)
            Analytics.trackAddToCart(this.carts[index], 1, this.active_currency);

            // CRITICAL FIX: Backend already returns updated data, use it directly
            // Set flag to prevent watcher from triggering
            this.isInternalUpdate = true;
            this.$store.dispatch('carts', response.data.carts);
            this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
          }
        }).catch((error) => {
          this.disable = false;
        })


      } else {
        toastr.warning(this.lang.Only + ' ' + this.carts[index].current_stock + ' ' + this.lang.items_available_at_this_time, this.lang.Warning + ' !!');
      }
    },
    cartMinus(index) {

      if (this.disable) {
        return false;
      }

      if (this.payment_form.quantity[index].quantity > this.carts[index].min_quantity) {
        let formData = {
          id: this.carts[index].id,
          quantity: -1,
          status: 'minus',
        };

        let url = this.getUrl('cart/update');
        this.disable = true;

        axios.post(url, formData).then((response) => {
          this.disable = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            // Analytics: Track remove from cart (1 quantity removed)
            Analytics.trackRemoveFromCart(this.carts[index], 1, this.active_currency);

            // CRITICAL FIX: Backend already returns updated data, use it directly
            // Set flag to prevent watcher from triggering
            this.isInternalUpdate = true;
            this.$store.dispatch('carts', response.data.carts);
            this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
          }
        })


      } else {
        if (this.carts[index].quantity === 1) {
          this.deleteCart(this.carts[index].id);
        }else{
          toastr.warning(this.lang.please_order_minimum_of + ' ' + this.carts[index].min_quantity + ' ' + this.lang.Quantity, this.lang.Warning + ' !!');
        }
      }


    },

    applyCoupon() {
      // Validate cart has products before applying coupon
      if (!this.cartList || this.cartList.length === 0) {
        toastr.error('Your cart is empty. Add products to apply coupon.', this.lang.Error + " !!");
        return;
      }

      let url = this.getUrl("user/apply_coupon");
      if (this.cartList[0] && this.cartList[0].trx_id) {
        this.payment_form.trx_id = this.cartList[0].trx_id;
      } else {
        this.payment_form.trx_id = this.trx_id;
      }

      this.loading = true;
      axios
          .post(url, this.payment_form)
          .then((response) => {
            this.loading = false;
            if (response.data.error) {
              toastr.error(response.data.error, this.lang.Error + " !!");
            } else {
              toastr.success(response.data.success, this.lang.Success + " !!");
              // CRITICAL FIX: Backend already returns updated data, use it directly
              // Set flag to prevent watcher from triggering
              this.isInternalUpdate = true;
              this.$store.dispatch('carts', response.data.carts);
              this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
              this.payment_form.coupon_code = "";
            }
          })
          .catch((error) => {
            this.loading = false;
            toastr.success("Something Went Wrong", "Error !!");
          });
    },
    removeCoupon(id) {
      if (confirm("Are You Sure ?")) {
        let url = this.getUrl("user/coupon-delete");
        this.disabled = true;
        let form = {
          trx_id: this.cartList[0].trx_id,
          coupon_id: id,
          user_id: this.authUser.id,
        };

        axios
            .post(url, form)
            .then((response) => {
              this.disabled = false;
              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
              } else {
                toastr.success(response.data.success, this.lang.Success + " !!");
                // CRITICAL FIX: Backend already returns updated data, use it directly
                // Set flag to prevent watcher from triggering
                this.isInternalUpdate = true;
                this.$store.dispatch('carts', response.data.carts);
                this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
                this.payment_form.coupon_code = "";
              }
            })
            .catch((error) => {
              this.disabled = false;
              toastr.success("Something Went Wrong", "Error !!");
            });
      }
    },
    removeAllCoupons() {
      // Automatically remove all coupons when cart becomes empty
      if (!this.coupon_list || this.coupon_list.length === 0) {
        return; // No coupons to remove
      }

      let url = this.getUrl("user/coupon-delete");
      let couponIds = this.coupon_list.map(coupon => coupon.coupon_id);

      if (couponIds.length === 0) {
        return;
      }

      // Remove all coupons
      couponIds.forEach(couponId => {
        let form = {
          trx_id: this.cartList && this.cartList[0] ? this.cartList[0].trx_id : this.trx_id,
          coupon_id: couponId,
          user_id: this.authUser ? this.authUser.id : null,
        };

        axios.post(url, form).then((response) => {
          if (!response.data.error) {
            // Clear local coupon data
            this.payment_form.coupon_discount = 0;
            this.coupon_list = [];

            // Recalculate totals without coupon discount
            if (this.settings.tax_type == 'after_tax' && this.settings.vat_and_tax_type == 'order_base') {
              this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.shipping_tax)) - parseFloat(this.payment_form.discount_offer));
              this.payment_form.total += this.payment_form.tax;
              // Ensure total is not negative
              if(this.payment_form.total < 0){
                this.payment_form.total = 0;
              }
            } else {
              this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - parseFloat(this.payment_form.discount_offer));
              // Ensure total is not negative
              if(this.payment_form.total < 0){
                this.payment_form.total = 0;
              }
            }

            // Final validation: Ensure total is not negative after removing coupons
            if (this.payment_form.total < 0) {
              this.payment_form.total = 0;
            }
          }
        }).catch((error) => {
          // Error removing coupon
        });
      });
    },

    /**
     * Validate product-wise coupons after cart operations
     * Removes coupons that are no longer valid because eligible products were removed
     * Returns Promise that resolves when validation is complete
     */
    validateProductWiseCoupons(carts, coupons, deletedProductId) {
      if (!coupons || coupons.length === 0) {
        return Promise.resolve();
      }

      if (!carts || carts.length === 0) {
        return this.removeAllCouponsSync();
      }

      // Get current product IDs in cart
      const cartProductIds = carts.map(c => c.product_id);

      // Check each product-wise coupon
      const couponsToRemove = [];
      coupons.forEach(coupon => {
        // Debug: Check coupon structure
        if (coupon.product_ids && coupon.product_ids.length > 0) {
          // Check if this is a product-wise coupon
          // Handle both 'product_base' and null/undefined type values
          const isProductWise = coupon.type === 'product_base' ||
                               (!coupon.type && coupon.product_ids && coupon.product_ids.length > 0);

          if (isProductWise) {
            // Check if any eligible product still exists in cart
            const hasEligibleProduct = coupon.product_ids.some(pid => cartProductIds.includes(pid));

            if (!hasEligibleProduct) {
              // No eligible products remaining, remove this coupon
              couponsToRemove.push(coupon.coupon_id);
            }
          }
        }
      });

      // Remove invalid coupons and refresh cart
      if (couponsToRemove.length > 0) {
        const removePromises = couponsToRemove.map(couponId => {
          let url = this.getUrl("user/coupon-delete");
          let form = {
            trx_id: this.cartList && this.cartList[0] ? this.cartList[0].trx_id : this.trx_id,
            coupon_id: couponId,
            user_id: this.authUser ? this.authUser.id : null,
          };

          return axios.post(url, form);
        });

        // Wait for all coupons to be removed, then refresh cart
        return Promise.all(removePromises).then(() => {
          toastr.info('Coupon removed: No eligible products in cart', 'Info');
          // Refresh cart data to show updated state
          return this.getCheckout();
        }).catch(() => {
          // Silently fail
        });
      }

      return Promise.resolve();
    },

    /**
     * Synchronous version of removeAllCoupons that returns a Promise
     */
    removeAllCouponsSync() {
      if (!this.coupon_list || this.coupon_list.length === 0) {
        return Promise.resolve();
      }

      let url = this.getUrl("user/coupon-delete");
      let couponIds = this.coupon_list.map(coupon => coupon.coupon_id);

      if (couponIds.length === 0) {
        return Promise.resolve();
      }

      const removePromises = couponIds.map(couponId => {
        let form = {
          trx_id: this.cartList && this.cartList[0] ? this.cartList[0].trx_id : this.trx_id,
          coupon_id: couponId,
          user_id: this.authUser ? this.authUser.id : null,
        };
        return axios.post(url, form);
      });

      return Promise.all(removePromises).then(() => {
        this.payment_form.coupon_discount = 0;
        this.coupon_list = [];
        // Recalculate totals
        if (this.settings.tax_type == 'after_tax' && this.settings.vat_and_tax_type == 'order_base') {
          this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.shipping_tax)) - parseFloat(this.payment_form.discount_offer));
          this.payment_form.total += this.payment_form.tax;
          if(this.payment_form.total < 0){
            this.payment_form.total = 0;
          }
        } else {
          this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - parseFloat(this.payment_form.discount_offer));
          if(this.payment_form.total < 0){
            this.payment_form.total = 0;
          }
        }
      });
    },

    async confirmOrder() {

      try{
        this.trx_id = this.cartList[0].trx_id

        if (this.authUser && this.authUser.user_type !== 'customer') {
          return toastr.warning(this.lang.you_are_not_able_topurchase_products, this.lang.Warning + ' !!');
        }

        // Check if already submitting
        if (this.loading) {
          return;
        }

        // Start loading
        this.loading = true;
        this.$Progress.start();
        this.payment_form.payment_method = this.payment_method

        // Validate checkout summary
        if (this.payment_form.sub_total <= 0) {
          toastr.error('Checkout summary is invalid.', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        if (!this.terms_condition) {
          toastr.error('You must agree to terms and condition', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        if (!this.form.thana_id) {
          toastr.error('Invalid Address', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        if (!this.form.phone_no) {
          toastr.error('Empty Phone Number', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        if (!this.form.name) {
          toastr.error('Name is required', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        // if (!this.form.email) {
        //   toastr.error('Email is required', this.lang.Error + " !!");
        //   this.$Progress.fail();
        //   return;
        // }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(this.form.email)) {
          toastr.error('Please enter a valid email address.', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        // Final validation: Ensure total is not negative before submitting order
        if (this.payment_form.total < 0) {
          toastr.error('Order total cannot be negative. Please review your discounts.', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        // Ensure coupon discount doesn't cause negative total
        const maxAllowedCouponDiscount = this.payment_form.sub_total + this.payment_form.tax + this.payment_form.shipping_tax - this.payment_form.discount_offer;
        if (this.payment_form.coupon_discount > maxAllowedCouponDiscount && this.payment_form.total <= 0) {
          toastr.error('Coupon discount exceeds order total. Please remove the coupon.', this.lang.Error + " !!");
          this.$Progress.fail();
          this.loading = false;
          return;
        }

        // Construct data to send
        const requestData = {
          trx_id: this.cartList[0].trx_id,
          payment_form: this.payment_form,
          deliveryMethod: this.deliveryMethod,
          store_id: this.deliveryMethod === STORE_PICK ? this.selectedStore : '',
          form: this.form,
        };

        // Analytics: Track begin checkout before API call
        Analytics.trackBeginCheckout(this.cartList, this.active_currency);

        // API call
        const url = this.getUrl('user/confirm-order');
        const response = await axios.post(url, requestData);

        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
          this.loading = false;
        } else {
          this.$Progress.finish();
          toastr.success('Order confirmed successfully!', 'Success');

          await this.takeOrders();

          if (this.payment_form.payment_method === 'online_payment') {
            // For online payment: redirect to SSLCOMMERZ payment initiation
            // The takeOrders() method will have fetched the order with the code
            // Redirect using trx_id only (code will be fetched from order)
            window.location.href = this.getUrl(
                "pay?trx_id=" + this.trx_id
            );
            return;
          }

          // For Cash on Delivery: complete the order
          // Keep loading state true during complete order process
          this.completeOrders();
          // Note: loading state will be reset in completeOrders catch block
          // or kept true until redirect happens
        }


      } catch(error) {
        this.$Progress.fail();
        toastr.error('Something went wrong. Please try again.', this.lang.Error + ' !!');
        console.error(error);
        this.loading = false;
      }


    },

    completeOrders()
    {

      let form = {
        payment_type: this.payment_form.payment_method,
        trx_id: this.trx_id,
        is_buy_now: this.$route.params.is_type ? this.$route.params.is_type : 0,
      };

      let url =  this.authUser
          ? this.getUrl('user/complete-order?code=' + this.code)
          : this.getUrl('user/complete-order?code=' + this.code + '&guest=1');

      axios
          .post(url, form, {
            transformRequest: [
              function (data, headers) {
                return objectToFormData(data);
              },
            ],
          })
          .then((response) => {

            if (response.data.error) {

              toastr.error(response.data.error, this.lang.Error + " !!");
              // Reset loading state on error so user can try again
              this.loading = false;

            } else {

              this.$store.dispatch('resetCart');

              // Keep loading state true during redirect
              if (this.code) {
                this.$router.push({
                  name: "get.invoice",
                  params: { orderCode: this.code },
                });
              } else {
                this.$router.push({
                  name: "invoice.list",
                  params: { trx_id: this.trx_id },
                });
              }
              // Loading state remains true until page navigates away

            }
          })
          .catch((error) => {
            // Reset loading state on error so user can try again
            this.loading = false;
            console.error('Complete order error:', error);
          });
    },

    formatCouponDiscount(coupon) {
      // Use the backend-calculated discount amount directly
      // The backend has already calculated this based on applicable_on_discount setting
      return parseFloat(coupon.discount || 0).toFixed(2);
    },

    recalculateCouponDiscounts() {
      // Recalculate all coupon discounts based on current subtotal
      if (!this.coupon_list || this.coupon_list.length === 0) {
        this.payment_form.coupon_discount = 0;
        return;
      }

      let totalCouponDiscount = 0;

      for (let i = 0; i < this.coupon_list.length; i++) {
        const coupon = this.coupon_list[i];
        let discount = 0;

        // Backend returns calculated discount in 'discount' field
        // We need to recalculate based on current subtotal
        const rawDiscountValue = parseFloat(coupon.coupon_discount || 0); // Raw value from coupon table

        if (coupon.discount_type === 'flat') {
          // Flat discount - use the calculated amount or recalculate
          discount = parseFloat(coupon.discount || rawDiscountValue || 0);
        } else if (coupon.discount_type === 'percent') {
          // Percent discount - calculate based on current subtotal
          const percent = rawDiscountValue; // e.g., 25 for 25%
          const subtotal = parseFloat(this.payment_form.sub_total || 0);
          discount = subtotal * (percent / 100);

          // Apply maximum discount cap if exists
          if (coupon.maximum_discount && discount > parseFloat(coupon.maximum_discount)) {
            discount = parseFloat(coupon.maximum_discount);
          }
        } else {
          // Fallback - use the calculated discount from backend
          discount = parseFloat(coupon.discount || 0);
        }

        // Ensure discount doesn't exceed subtotal
        if (discount > parseFloat(this.payment_form.sub_total || 0)) {
          discount = parseFloat(this.payment_form.sub_total || 0);
        }

        totalCouponDiscount += discount;
      }

      this.payment_form.coupon_discount = parseFloat(totalCouponDiscount.toFixed(2));

      // Recalculate total
      if (this.settings.tax_type == 'after_tax' && this.settings.vat_and_tax_type == 'order_base') {
        this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
        this.payment_form.total += this.payment_form.tax;
        if(this.payment_form.total < 0){
          this.payment_form.total = 0;
        }
      } else {
        this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
        if(this.payment_form.total < 0){
          this.payment_form.total = 0;
        }
      }
    },

    sanitizePhoneNumber(event) {
      let input = event.target.value;

      // Remove all invalid characters (keep digits, spaces, and +)
      input = input.replace(/[^+\d\s]/g, '');

      // Ensure only one '+' at the beginning
      if (input.includes('+')) {
        input = '+' + input.replace(/\+/g, '').trim();
      }

      // Update the v-model value
      this.form.phone_no = input;
    },

    trackCartView() {
      // Track cart view only once per page load, and only if cart has items
      if (this.carts && this.carts.length > 0 && !this.cartViewTracked) {
        Analytics.trackViewCart(this.carts, this.active_currency);
        this.cartViewTracked = true;
      }
    },


  },
};
</script>

<style>
.cart-new h4 {
    font-size: 18px;
    font-weight: 700;

}

.cart-new .form-control {
    margin-bottom: 24px !important;
}
.cart-table tbody tr {
  border-top: 1px solid black !important;
}

.cart-new table thead th {
    background: black !important;
    color: white !important;
    text-align: center !important;

}

.cart-new table tbody td {
    background: transparent !important;
    text-align: center !important;
}

.cart-new table tbody td:first-child {
    text-align: center !important;
}

.cart-new table tbody td:nth-child(2) {
    text-align: left !important;
}

.cart-new table tbody td:nth-child(2) span {
    text-align: left !important;
    display: block;
}

.cart-new p {
    margin-bottom: 0;
}

/* Mobile Table Responsive Styles */
@media screen and (max-width: 767px) {
  /* Hide only serial number column on mobile - keep Total and Action visible */
  .cart-table th:first-child,
  .cart-table td:first-child {
    display: none;
  }

  .cart-table th,
  .cart-table td {
    padding: 8px 3px !important;
    font-size: 11px !important;
  }

  /* Adjust column widths for mobile - 5 columns now (Product, Price, Qty, Total, Action) */
  .cart-table th:nth-child(2),
  .cart-table td:nth-child(2) {
    width: 35%;
    min-width: 100px;
  }

  .cart-table th:nth-child(3),
  .cart-table td:nth-child(3) {
    width: 20%;
  }

  .cart-table th:nth-child(4),
  .cart-table td:nth-child(4) {
    width: 20%;
  }

  .cart-table th:nth-child(5),
  .cart-table td:nth-child(5) {
    width: 15%;
  }

  .cart-table th:nth-child(6),
  .cart-table td:nth-child(6) {
    width: 10%;
  }

  /* Adjust quantity spinner for mobile */
  .product-quantity .quantity {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .product-quantity .quantity .input-text {
    width: 30px;
    height: 30px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 0;
    font-size: 11px;
    padding: 0;
  }

  .product-quantity .quantity .btn {
    width: 24px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ddd;
    background: #f8f9fa;
    cursor: pointer;
    padding: 0;
  }

  .product-quantity .quantity .btn:hover {
    background: #168FC3;
    color: white;
  }

  .product-quantity .quantity .btn span {
    font-size: 12px;
  }

  /* Adjust delete button size */
  .cart-table td:last-child img {
    width: 16px;
    height: 16px;
  }
}

@media screen and (max-width: 575px) {
  .cart-table th,
  .cart-table td {
    padding: 6px 2px !important;
    font-size: 10px !important;
  }

  .cart-table th:nth-child(2),
  .cart-table td:nth-child(2) {
    min-width: 80px;
    font-size: 10px !important;
  }

  .product-quantity .quantity .input-text {
    width: 28px;
    height: 26px;
    font-size: 10px;
  }

  .product-quantity .quantity .btn {
    width: 22px;
    height: 26px;
  }

  .product-quantity .quantity .btn span {
    font-size: 11px;
  }

  .cart-table td:last-child img {
    width: 14px;
    height: 14px;
  }
}

@media screen and (max-width: 480px) {
  .cart-table th,
  .cart-table td {
    padding: 5px 1px !important;
    font-size: 9px !important;
  }

  .cart-table th:nth-child(2),
  .cart-table td:nth-child(2) {
    min-width: 70px;
    font-size: 9px !important;
  }
}

.checkout-card {
    background: white;
    border: none;
    border-radius: 5px;
    padding: 20px 40px;
}

.checkout-card h4 {
    margin-bottom: 20px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;

}

.checkout-card p {
    margin-bottom: 10px;
    font-size: 14px;
}

.checkout-card .promo-input {
    width: 75%;
    background: #DBDBDB;
    height: 30px;
    border: none;
    padding-left: 10px;
}

.checkout-card .promo-btn {
    width: 25%;
    background: #1E6AAF;
    color: white;
    padding: 0 10px;
    height: 30px;
    border: none;
}

.checkout-card span {
    width: 100%;
    height: 1.5px;
    background: #707070;
    display: block;
    margin: 16px 0;
}

.checkout-btn {
    background: #168FC3;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    display: block;
    text-decoration: none;
    outline: none;
    box-shadow: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.checkout-btn:hover:not(:disabled) {
    background: #147ab3;
    text-decoration: none;
}

.checkout-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.7;
    text-decoration: none;
}

.checkout-btn:disabled:hover {
    background: #ccc;
    text-decoration: none;
}

/* Coupon input and button disabled state */
.promo-input:disabled {
    background: #f0f0f0;
    color: #999;
    cursor: not-allowed;
    opacity: 0.6;
}

.promo-btn:disabled {
    background: #ccc;
    color: #999;
    cursor: not-allowed;
    opacity: 0.6;
}

.promo-btn:disabled:hover {
    background: #ccc;
    cursor: not-allowed;
}

/* Applied Coupons List */
.applied-coupons {
    background: #f8f9fa;
    border-radius: 5px;
    padding: 10px;
}

.applied-coupons .badge {
    font-size: 12px;
    padding: 5px 10px;
}

.applied-coupons img[alt="Remove"] {
    transition: opacity 0.3s ease;
}

.applied-coupons img[alt="Remove"]:hover {
    opacity: 1 !important;
}

.delivery-card {
    background: white;
    padding: 0px 10px;
    border-radius: 5px;
    width: 146px;
    cursor: pointer;
}

.delivery-card.active {
    background: #168FC3;
    color: white !important;
}

.delivery-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f0f0f0;
}

.payment-method p {
    margin-bottom: 10px;
    cursor: pointer;
    padding-left: 10px;
    overflow-wrap: break-word;
}

.payment-method .active {
    background: #168FC3;
    color: white !important;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
}

.link_section:hover {
    color: #168FC3 !important;
    text-decoration: none !important;
  }

/* Remove old 430px media query as it's replaced by card view */

/* v-select styling fixes */
.cart-new .v-select {
  position: relative;
  z-index: 10;
}

.cart-new .vs__dropdown-menu {
  z-index: 9999 !important;
  position: absolute !important;
  background: white !important;
  border: 1px solid #ddd !important;
  max-height: 200px !important;
  overflow-y: auto !important;
}

.cart-new .vs__dropdown-option {
  padding: 8px 12px !important;
  cursor: pointer !important;
}

.cart-new .vs__dropdown-option:hover {
  background-color: #f0f0f0 !important;
}

.cart-new .vs__selected {
  background-color: white !important;
}

/* Store Selection Styles */
.store-selection-item {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.store-selection-item:hover {
  background-color: #f8f9fa;
}

.store-selection-item.active {
  background-color: #168FC3;
  border-color: #168FC3;
  color: white !important;
}

.store-selection-item.active p.store-name {
  color: white !important;
}

.store-name {
  font-weight: 600;
  margin-bottom: 5px !important;
}

.store-details-info {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.3);
}

.store-details-info p {
  margin-bottom: 5px !important;
  font-size: 13px;
  line-height: 1.4;
}

.store-details-info p strong {
  font-weight: 600;
  margin-right: 5px;
}

/* Checkout card responsive adjustments */
@media screen and (max-width: 991px) {
  .checkout-card {
    padding: 15px 20px;
    margin-top: 20px;
  }

  .checkout-card h4 {
    font-size: 14px;
  }

  .checkout-card p {
    font-size: 13px;
  }

  /* Form responsive for address section */
  .cart-new .col-lg-6,
  .cart-new .col-md-6 {
    width: 100%;
    max-width: 100%;
    flex: 0 0 100%;
    padding-left: 15px;
    padding-right: 15px;
  }

  .cart-new .form-control {
    font-size: 14px;
  }
}

@media screen and (max-width: 767px) {
  .checkout-card {
    padding: 15px;
  }

  .checkout-card h4 {
    font-size: 13px;
    margin-bottom: 15px;
  }

  .checkout-card p {
    font-size: 12px;
  }

  /* Delivery method section responsive */
  .cart-new .row .col-lg-3 {
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }

  .cart-new .row .col-lg-9 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  /* Store selection items */
  .store-selection-item {
    width: 100%;
    padding: 12px;
    margin-bottom: 8px;
  }

  /* Applied coupons responsive */
  .applied-coupons .badge {
    font-size: 11px;
    padding: 3px 8px;
  }

  .applied-coupons small {
    font-size: 10px;
  }
}

@media screen and (max-width: 575px) {
  /* Form responsive for address section */
  .cart-new .col-lg-6,
  .cart-new .col-md-6,
  .cart-new .col-md-12 {
    padding-left: 10px;
    padding-right: 10px;
  }

  .cart-new .form-control {
    font-size: 13px;
    padding: 8px 12px;
  }

  .cart-new p {
    font-size: 13px;
  }

  .checkout-btn {
    font-size: 13px;
    padding: 10px 6px;
    height: 42px;
  }

  .link_section {
    font-size: 12px;
  }

  /* Delivery section - display horizontally on mobile like payment methods */
  .cart-new .row .col-lg-3 {
    flex: 0 0 auto;
    max-width: none;
    margin-bottom: 0;
    margin-right: 10px;
  }

  .cart-new .row .col-lg-9 {
    flex: 1;
    max-width: none;
  }
}
</style>
