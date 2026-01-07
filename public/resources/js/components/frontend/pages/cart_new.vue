<template>
    <div class="container cart-new">
        <h4 class="mt-3">SHOPPING CART</h4>
        <div class="row">
            <div class="col-lg-8">
              <table class="cart-table">
                <thead>
                <tr>
                  <th>Sl.</th>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(cart, index) in carts" :key="cart.id">
                  <td>{{ index + 1 }}</td>
                  <td class="d-flex flex-column align-items-start">
                    <span>{{ cart.product_name }}</span>
                  </td>
                  <td>
                    <div v-if="cart.discount > 0">
                      <del>{{ priceFormat((cart.price)) }}</del>
                    </div>
                    <span>{{ priceFormat(cart.price - cart.discount) }}</span>
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
                  <td>{{ priceFormat((cart.price - cart.discount) * payment_form.quantity[index].quantity) }}</td>
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
                          v-model="form.name" 
                          >
                    </div>
                    <div class="col-lg-6">
                        <p>Phone Number <span class="text-danger">*</span></p>
                        <input 
                          type="text" 
                          class="form-control" 
                          placeholder="Phone Number"
                          v-model="form.phone_no" 
                        >
                    </div>
                    <div class="col-lg-6">
                        <p>Email <span class="text-danger">*</span></p>
                        <input 
                          type="email"
                          class="form-control" 
                          placeholder="Email"
                          v-model="form.email"
                        >
                    </div>

                  <div class="col-lg-6">
                    <div >
                      <p >Division  <span class="text-danger">*</span></p>
                      <v-select 
                        label="name"  
                        placeholder="Select a Division" 
                        :options="divisions" 
                        v-model="form.division_id" 
                        :reduce="(option) => option.id" 
                        @input="getStates()" 
                        :class="{ 'error_border' : errors.division_id }"
                        >
                      </v-select>
                    </div>
                    <span class="validation_error" v-if="errors.division_id">{{ errors.division_id[0] }}</span>
                  </div>

                  <div class="col-md-6">
                    <div >
                      <p >District  <span class="text-danger">*</span></p>
                      <v-select 
                        label="name" 
                        placeholder="Select a District" 
                        :options="states" 
                        v-model="form.district_id" 
                        :reduce="(option) => option.id" 
                        @input="getCities()" 
                        :class="{ 'error_border' : errors.district_id }"
                        >
                      </v-select>
                    </div>
                    <span class="validation_error" v-if="errors.district_id">{{ errors.state_id[0] }}</span>
                  </div>

                  <div class="col-md-6">
                    <div>
                      <p >Thana  <span class="text-danger">*</span></p>
                      <v-select 
                        label="name" 
                        placeholder="Select a Thana" 
                        :options="cities" 
                        v-model="form.thana_id" 
                        :reduce="(option) => option.id" 
                        @input="getDeliveryCharge()"
                        :class="{ 'error_border' : errors.thana_id }"
                        >
                      </v-select>
                    </div>
                    <span class="validation_error"
                          v-if="errors.city_id">{{ errors.thana_id[0] }}</span>
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
                            :class="{ active: deliveryMethod === option }" @click="setDeliveryActiveOption(index)">
                            <p @change="getDeliveryCharge">{{ option }}</p>
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
                        <p>৳ {{ payment_form.shipping_tax }}</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Discount </p>
                        <p>৳ {{ payment_form.discount_offer }}</p>
                    </div>
                  <div class="d-flex justify-content-between">
                        <p>Coupon Discount </p>
                        <p>৳ {{ payment_form.coupon_discount }}</p>
                    </div>

                    <form @submit.prevent="applyCoupon">
                      <div class="d-flex justify-content-center">

                          <input
                              type="text"
                              class="promo-input"
                              v-model="payment_form.coupon_code"
                              placeholder="Coupon Code"
                          />

                          <loading_button
                              v-if="loading"
                              :class_name="'opacity_5'">
                          </loading_button>
                          <button v-else class="promo-btn">Promo</button>
                      </div>
                    </form>

                    <span></span>
                    <div class="d-flex justify-content-between">
                        <h4>Total Payable</h4>
                        <h4>৳ {{ payment_form.total }}</h4>
                    </div>
                    <div class="d-flex flex-column justify-content-center">

                        <div class="d-flex justify-content-center mb-2">
                            <input id="terms_condition" v-model="terms_condition" type="checkbox">
                            <label for="terms_condition" class=" text-muted mb-0 ms-2">I agree to the terms and condition</label>

                        </div>
                        <button 
                          class="checkout-btn"
                          @click="confirmOrder"
                          >Confirm Order
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

</template>

<script>
import {EXPRESS_DELIVERY, STANDARD, STORE_PICK} from "../../../constants/deliveryMethod";

export default {
  mounted() {

    if(!this.cartList)
    {
      this.$router.push({name: 'home'});
    }

    this.getDivisions()
    this.getCheckout()
  },
  watch: {
    cartList(newValue, oldValue) {
      this.getCheckout();
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
  },
  data() {
      return {
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
        is_shimmer: false,
        coupon_list: [],
        shipping_classes: [],

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

        divisions: [],
        states: [],
        cities: [],
        address_submit_loading: false
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
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          this.divisions = response.data.divisions;
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

      this.form.district_id = "";
      this.form.thana_id = "";
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
        }
      });

      this.form.thana_id = "";

    },
    getDeliveryCharge(){

      this.shipping_cost = 0

      if (this.settings.shipping_fee_type === 'area_base') {
        let url = this.getUrl('user/find/shipping_cost');

        let form = {
          city_id: this.form?.thana_id,
          deliveryMethod: this.deliveryMethod
        };

        axios.post(url, form).then((response) => {

          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.payment_form.shipping_tax = response.data.shipping_cost;
            this.shipping_cost = this.payment_form.shipping_tax;
            this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
          }
        });
      }

    },
    setDeliveryActiveOption(index)
    {
        if(this.deliveryOptions[index]){
          this.deliveryMethod = this.deliveryOptions[index];
        }
        this.getDeliveryCharge();
    },
    setActivePaymentOption(index) {
        if(this.paymentOptions[index]){
          this.payment_method = this.paymentOptions[index].value;
        }
    },
    getCheckout() {
      this.$Progress.start();
      let url = this.getUrl('cart/list?cart_page=1');
      axios.get(url).then((response) => {
        this.is_shimmer = true;
        this.$store.commit('setShimmer', 0);
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          let checkouts = response.data.checkouts;
          let coupons = response.data.coupons;
          this.shipping_classes = response.data.shipping_classes;
          this.parseData(this.cartList, checkouts, coupons);
        }
      })
    },
    deleteCart(id) {
      if (confirm("Are you sure?")) {
        let url = this.getUrl('cart/delete/' + id);
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.$store.dispatch('carts', response.data.carts);
          }
        })
      }
    },
    checkout() {
      if (!this.authUser) {
        toastr.error(this.lang.login_first, this.lang.Error + ' !!');
        this.$store.commit('setLoginRedirection', this.$route.name);
        this.$router.push({name: 'login'});
        return false;
      }
      if (this.authUser.user_type != 'customer') {
        return toastr.warning(this.lang.you_are_not_able_topurchase_products, this.lang.Warning + ' !!');
      }
      this.$router.push({name: 'checkout'});
    },
    parseData(carts, checkouts, coupons) {
      this.resetForm();
      this.collapseAttribute = [];
      this.carts = [];

      if (carts) {
        for (let i = 0; i < carts.length; i++) {
          this.payment_form.quantity.push({id: carts[i].id, quantity: carts[i].quantity});
          this.carts.push(carts[i]);
          this.payment_form.sub_total += parseFloat(carts[i].price * carts[i].quantity);
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
          if (this.settings.coupon_system == 1) {
            this.payment_form.coupon_discount += parseFloat(checkouts[key].discount);
          }
        }
      }

      if (coupons && this.settings.coupon_system == 1) {
        this.coupon_list = coupons;
        for (let i = 0; i < coupons.length; i++) {
          this.payment_form.coupon_discount += parseFloat(coupons[i].discount);
        }
      }

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
            this.$store.dispatch('carts', response.data.carts);
            let coupons = response.data.coupons;
            this.parseData(this.cartList, response.data.checkouts, coupons);
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
            this.$store.dispatch('carts', response.data.carts);
            let coupons = response.data.coupons;
            let checkouts = response.data.checkouts;
            this.parseData(this.cartList, checkouts, coupons);
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
              this.carts = [];
              let carts = response.data.carts;
              let checkouts = response.data.checkouts;
              let coupons = response.data.coupons;
              this.parseData(carts, checkouts, coupons);
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
                this.carts = [];
                let carts = response.data.carts;
                let checkouts = response.data.checkouts;
                let coupons = response.data.coupons;
                this.parseData(carts, checkouts, coupons);
                this.payment_form.coupon_code = "";
              }
            })
            .catch((error) => {
              this.disabled = false;
              toastr.success("Something Went Wrong", "Error !!");
            });
      }
    },

    async confirmOrder() {

      try{
        this.trx_id = this.cartList[0].trx_id

        if (this.authUser && this.authUser.user_type !== 'customer') {
          return toastr.warning(this.lang.you_are_not_able_topurchase_products, this.lang.Warning + ' !!');
        }

        this.$Progress.start();
        this.payment_form.payment_method = this.payment_method

        // Validate checkout summary
        if (this.payment_form.sub_total <= 0) {
          toastr.error('Checkout summary is invalid.', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.terms_condition) {
          toastr.error('You must agree to terms and condition', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.thana_id) {
          toastr.error('Invalid Address', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.phone_no) {
          toastr.error('Empty Phone Number', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.name) {
          toastr.error('Name is required', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.email) {
          toastr.error('Email is required', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        // Construct data to send
        const requestData = {
          trx_id: this.cartList[0].trx_id,
          payment_form: this.payment_form,
          deliveryMethod: this.deliveryMethod,
          form: this.form,
        };

        // API call
        const url = this.getUrl('user/confirm-order');
        const response = await axios.post(url, requestData);

        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          toastr.success('Order confirmed successfully!', 'Success');

          await this.takeOrders();

          if (this.payment_form.payment_method === 'online_payment') {
            return (window.location.href = this.getUrl(
                "get/ssl-response?payment_type=ssl_commerze&code=" +
                this.code +
                "&trx_id=" +
                this.trx_id
            ));
          }

          this.completeOrders();
        }


      } catch(error) {
        this.$Progress.fail();
        toastr.error('Something went wrong. Please try again.', this.lang.Error + ' !!');
        console.error(error);
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

            } else {

              this.$store.dispatch('resetCart');

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

            }
          })
          .catch((error) => {
            this.loading = false;
          });
    }


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

.cart-new p {
    margin-bottom: 0;
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

.payment-method p {
    margin-bottom: 10px;
    cursor: pointer;
    padding-left: 10px;
}

.payment-method .active {
    background: #168FC3;
    color: white !important;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
}

@media screen and (max-width: 430px) {
  .cart-new table>tbody>tr>td, table>tbody>tr>th, table>tfoot>tr>td, table>tfoot>tr>th, table>thead>tr>td, table>thead>tr>th{
    padding: 15px 3px !important;
    font-size: 14px !important;
  }

}
</style>