<template>
  <div class="container" v-if="productDetails.id">
    <div class="row product-details-card">
      <div class="col-lg-5" v-if="lengthCounter(productDetails.gallery) > 0">
        <div class="product-slider-section slider-arrows">

          <div class="">
            <div class="product-image">
              <img @click="index = current_index" :src="large_image" :alt="productDetails.product_name" />
            </div>

            <VueSlickCarousel v-bind="slick_settings" :rtl="settings.text_direction == 'rtl'">
              <div v-for="(image, small_image_index) in productDetails.gallery.small" :key="small_image_index"
                :class="{ active: small_image_index == current_index }"
                class="thumb-item "
                @click.prevent="activeImage(small_image_index)"
                @touchend.prevent="activeImage(small_image_index)">
                <div class="thumbnail-img">
                  <img :src="image" :alt="productDetails.product_name" />
                </div>
              </div>
            </VueSlickCarousel>
          </div>

          <!-- Campaign Badge -->
          <span v-if="productDetails.campaign_price && productDetails.campaign_price.badge_text"
                class="base campaign-badge"
                :style="{ backgroundColor: productDetails.campaign_price.badge_color || '#ff0000' }">
            {{ productDetails.campaign_price.badge_text }}
          </span>
          <!-- Special Discount Badge -->
          <span class="base" v-else-if="productDetails.special_discount_check > 0">
            {{
              productDetails.special_discount_type == "flat"
                ? priceFormat(productDetails.special_discount_check) +
                " " +
                lang.off
                : productDetails.special_discount_check + "% " + lang.off
            }}
          </span>
        </div>
      </div>

      <div class="col-lg-7">
        <h4 class="mb-2">{{ productDetails.product_name }}</h4>
        <div class="d-flex flex-wrap gap-2">
          <div class="tab-content">
            <h4>Brand : {{ productDetails.brand.title }}</h4>
          </div>
          <div class="tab-content">
            <h4>SKU : {{ stockFind().sku }} </h4>
          </div>
          <div class="tab-content">
            <h4>Status : {{ productDetails.current_stock > 0 ? 'In Stock' : 'Stock Out' }}</h4>
          </div>
          <div class="tab-content">
            <h4>QTY: {{ productDetails.current_stock }} Units left </h4>
          </div>
        </div>
        <div class="keyfeatures mt-3">
          <h4>Key Features</h4>
          <div v-html="productDetails?.short_description"></div>
        </div>

<!--        <p class="text-start" v-if="productDetails.has_variant">-->
<!--          {{ productDetails.variation_price }}-->
<!--        </p>-->
<!--        price range for variant-->

        <div
            class="sg-product-color"
            v-if="
                  productDetails.product_colors &&
                  productDetails.colors.length > 0
                "
        >
          <div class="sg-color">
            <h5>{{ lang.color }}:</h5>
            <div
                v-for="(color, index) in productDetails.product_colors"
                :key="'color' + index"
            >
              <input
                  type="radio"
                  value="color1"
                  :id="'color' + color.id"
                  v-model="product_form.color_id"
                  :value="color.id"
                  @change="attributeSelect($event.target)"
              />
              <label :for="'color' + color.id">
                <span :style="'background:' + color.code"></span>
              </label>
            </div>
          </div>
        </div>
        <!-- sg-product-color -->
        <div
            class="sg-product-size"
            v-for="(attribute, attribute_index) in attributes"
            :key="'attribute' + attribute_index"
            v-if="attributes.length > 0"
        >
          <div class="sg-size">
            <h5>{{ attribute.title }}:</h5>
            <form action="#">
              <div
                  v-for="(
                        value, value_index
                      ) in productDetails.attribute_values"
                  :key="'value' + value_index"
                  v-if="value.attribute_id == attribute.id"
              >
                <input
                    type="radio"
                    :id="attribute.id + '_attribute_' + value.id"
                    :value="value.id"
                    v-model="product_form.attribute_values[attribute_index]"
                    @change="
                          attributeSelect($event.target, attribute.id, value.id)
                        "
                    :disabled="checkDisable(attribute_index, value)"
                />
                <label
                    :for="attribute.id + '_attribute_' + value.id"
                    :class="{
                          disabled_radio: checkDisable(attribute_index, value),
                        }"
                >{{ value.value }}</label
                >
              </div>
            </form>
          </div>
        </div>

        <h4 v-if="productDetails?.warrenty" style="text-transform: capitalize;">{{ productDetails?.warrenty }} Official Warranty. </h4>

        <button v-if="productDetails?.free_shipping === 1 || productDetails?.free_shipping === true" class="product-details-card-button buy-now">Free Delivery</button>

        <!-- Campaign Badge (shown below image) -->
        <div v-if="productDetails.campaign_price && productDetails.campaign_price.badge_text"
             class="campaign-badge-section mb-2">
          <span class="badge badge-lg"
                :style="{ backgroundColor: productDetails.campaign_price.badge_color || '#ff0000', color: 'white', fontSize: '14px', padding: '8px 16px' }">
            {{ productDetails.campaign_price.badge_text }}
          </span>
        </div>

        <div class="price-section d-flex mt-3">
          <span class="space"></span>
          <div class="d-flex flex-column justify-content-center ms-3">
            <div class="d-flex gap-2 align-items-center">
              <h4 class="current-price">
                <!-- Campaign Price (Highest Priority) -->
                <!-- Event active: Campaign price (main) -->
                <!-- campaign_price is an object with {price, original_price, formatted_discount, badge_text, badge_color} -->
                <template v-if="productDetails.campaign_price && productDetails.campaign_price.price && parseFloat(productDetails.campaign_price.price) < parseFloat(productDetails.price)">
                  {{ priceFormat(productDetails.campaign_price.price) }}
                </template>
                <!-- Special Discount Price (Only when NO campaign is active) -->
                <!-- General discount: Discounted price (main) -->
                <template v-else-if="productDetails.special_discount_check > 0">
                  {{ priceFormat(productDetails.discount_percentage) }}
                </template>
                <!-- Regular Price (No discount) -->
                <template v-else>
                  {{ priceFormat(productDetails.price) }}
                </template>
              </h4>
              <!-- Original Price with Strikethrough (shown for any discount) -->
              <template v-if="(productDetails.campaign_price && productDetails.campaign_price.price && parseFloat(productDetails.campaign_price.price) < parseFloat(productDetails.price)) || productDetails.special_discount_check > 0">
                <span>|</span>
                <del><h4>{{ priceFormat(productDetails.price) }}</h4></del>
              </template>
            </div>
            <p>
              <!-- Campaign Discount Label -->
              <template v-if="productDetails.campaign_price && productDetails.campaign_price.price && parseFloat(productDetails.campaign_price.price) < parseFloat(productDetails.price)">
                {{ productDetails.campaign_price.formatted_discount || '' }} {{ lang.off || 'OFF' }} - {{ lang.campaign_offer || 'Campaign Offer' }}
              </template>
              <!-- Special Discount Label -->
              <template v-else-if="productDetails.special_discount_check > 0">
                {{ productDetails.special_discount_type == "flat"
                    ? priceFormat(productDetails.special_discount_check) + " " + lang.off
                    : productDetails.special_discount_check + "% " + lang.off
                }} - {{ lang.discount_offer || 'Discount Offer' }}
              </template>
              <!-- Regular Price Label -->
              <template v-else>
                {{ lang.regular_price || 'Regular Price' }}
              </template>
            </p>
            <p>{{ lang.online_payment || 'Online / Cash Payment' }}</p>
          </div>
        </div>


        <div class="w-full">
          <div v-if="productDetails.current_stock > 0" class="d-flex gap-2 align-items-center mt-3 mb-3 mb-md-0">
            <div class="d-flex gap-4 align-items-center quantity">
              <span class="icon" @click="cartMinus()">-</span>
              <span>{{ product_form.quantity }}</span>
              <span class="icon" @click="cartPlus()">+</span>
            </div>
            <button class="product-details-card-button add-cart ms-2"
              @click="addToCart(productDetails.minimum_order_quantity)">
              Add to Cart
            </button>
            <button @click="openDirectBuyModal(productDetails)" class="product-details-card-button buy-now">Buy Now</button>
          </div>

          <div v-else class="out-of-stock">
            <h4>Out Of Stock </h4>
          </div>
        </div>

      </div>

    </div>
    <direct_buy_modal ref="directBuyModal" />
  </div>
  <div class="row" v-else>
    <div class="col-lg-4">
      <VueSlickCarousel>
        <shimmer :height="450"></shimmer>

      </VueSlickCarousel>
    </div>
    <div class="col-lg-8">
      <div class="products-details-info">
        <div class="row">
          <div class="col-lg-9">
            <shimmer class="al-height mb-3" :height="20"></shimmer>
            <shimmer class="al-height mb-3" :height="20"></shimmer>
            <shimmer class="al-height mb-3" :height="20"></shimmer>
            <shimmer class="al-height mb-3" :height="20"></shimmer>

          </div>
        </div>

        <div class="stock-delivery">
          <shimmer class="de-margin" :height="100"></shimmer>
        </div>
        <div class="row">
          <div class="col-lg-4" v-for="(list, i) in 15" :key="i">
            <shimmer class="al-height mb-3" :height="20"></shimmer>
          </div>
        </div>
      </div>
    </div>


  </div>

 

</template>

<script>
import VueSlickCarousel from "vue-slick-carousel";
import shimmer from "../partials/shimmer";
import StarRating from "../partials/StarRating.vue";
import loading_button from "../partials/loading_button";
import productVideo from "../partials/product-video";
import single_seller from "../partials/single_seller";
import 'vue-toastr-2/dist/vue-toastr-2.min.css';
import Direct_buy_modal from "./direct_buy_modal.vue";



export default {
  name: "details-view",
  components: {
    VueSlickCarousel,
    shimmer,
    StarRating,
    loading_button,
    productVideo,
    single_seller,
    Direct_buy_modal
  },
  props: ["productDetails"],
  data() {
    return {
      isModalVisible: false, // Set to true to render the modal

      clickedSlide: 0,
      currentCarousel: "0",
      added_to_cart: false,
      firstStock: {
        stock: 0,
        sku: "",
        price: 0,
        special_discount_check: 0,
      },
      stock_status: '',
      slick_settings: {
        dots: false,
        edgeFriction: 0.35,
        infinite: true,
        arrows: true,
        autoplay: false,
        slidesToShow: 5,
        slidesToScroll: 5,
        // adaptiveHeight: true,
        centerPadding: "30px",
        responsive: [
          {
            breakpoint: 1024,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3,
            },
          },
          {
            breakpoint: 991,
            settings: {
              slidesToShow: 7,
              slidesToScroll: 7,
            },
          },
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 6,
              slidesToScroll: 3,
            },
          },
          {
            breakpoint: 480,
            settings: {
              slidesToShow: 5,
              slidesToScroll: 5,
            },
          },
          {
            breakpoint: 320,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3,
            },
          },
        ],
      },
      activeNav: "details",
      hoveredReview: 0,
      reply_form: 0,
      replies: 0,
      paginate: 1,
      edit: false,
      review_loading: false,
      like_loading: false,
      reply_loading: false,
      percentages: [],
      reviews: {
        data: [],
        total: 0,
      },
      total_price: 0,
      productView: {
        slug: this.$route.params.slug,
      },
      disable: false,
      days: 0,
      hours: 0,
      minutes: 0,
      seconds: 0,
      index: null,
      large_image: "",
      current_index: 0,
      colors: [],
      attribute_list: [],
      attribute_values: [],
      attribute_selector: 0,
      selected_stock: [],
      allowed_attributes: [],
      attributes_fetched: false,

      //new fields
      selectedColor: null,
    };
  },

  mounted() {
    if (this.productDetails && this.productDetails.form) {
      document.title = this.productDetails.product_name;
      if (this.productDetails.special_discount_check > 0) {
        this.setCountDown();
      }
      this.product_form.quantity = this.productDetails.form.quantity;
      this.product_form.variants_ids = this.productDetails.form.variants_ids;
      this.product_form.variants_name = this.productDetails.form.variants_name;
      this.product_form.id = this.productDetails.form.id;
      this.product_form.color_id = this.productDetails.form.color_id;
      this.product_form.attribute_values =
        this.productDetails.form.attribute_values;
      this.large_image = this.productDetails.gallery.large[0];
      if (this.productDetails.attribute_selector == 1) {
        this.getAttributes();
      }
    }
  },

  watch: {
    productDetails() {

      if (this.productDetails && this.productDetails.form) {
        // Debug: Log campaign pricing data
        console.log('[Product Details Card] Campaign Pricing Check:', {
          product_name: this.productDetails.product_name,
          price: this.productDetails.price,
          campaign_price: this.productDetails.campaign_price,
          campaign_price_value: this.productDetails.campaign_price?.price,
          has_campaign_discount: this.productDetails.has_campaign_discount,
        });

        if (this.productDetails.special_discount_check > 0) {
          this.setCountDown();
        }
        document.title = this.productDetails.product_name;
        this.product_form.quantity = this.productDetails.form.quantity;
        this.product_form.sku = this.productDetails.form.sku;
        this.product_form.variants_name =
          this.productDetails.form.variants_name;
        this.product_form.id = this.productDetails.form.id;
        this.large_image = this.productDetails.gallery.large[0];
        if (this.productDetails.attribute_selector == 1) {
          this.getAttributes();
        }
      }
    },
    index() {
    },
  },
  computed: {
    compareProducts() {
      return this.$store.getters.getUserCompare;
    },
    shimmer() {
      return this.$store.state.module.shimmer;
    },
    attributes() {
      return this.$store.getters.getProductAttributes;
    },
    carts() {
      let carts = this.$store.getters.getCarts;
      if (carts && carts[0]) {
        this.product_form.trx_id = carts[0].trx_id;
      }
      return carts;
    },
  },
  methods: {

    directAddToCart(product, directBuy = false) {

      if (this.productDetails.has_variant && !this.product_form.variants_ids) {
        return toastr.error(
            this.lang.please_select_all_attributes,
            this.lang.Error + " !!"
        );
      }

      this.product_form.id = product.id;

      let carts = this.carts;
      let url = this.getUrl("user/addToCart");

      axios.post(url, this.product_form).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
         
        } else {

          let carts = response.data.carts;

          if (directBuy) {
            if (!this.authUser) {
              this.$refs.directBuyModal.showOrderModal()
            }else{
              this.$router.push({ name: 'cartNew' });
            }
          }else {
            toastr.success(response.data.success, this.lang.Success + " !!");
          }
          this.$store.dispatch("carts", carts);
         

        }
      });
    },

    openDirectBuyModal(product) {

      // this.$refs.directBuyModal.showOrderModal()
      this.directAddToCart(product, true)

      // if (!this.authUser) {
      //   this.isModalVisible = true; // Trigger rendering if conditionally shown
      //   this.$nextTick(() => {
      //     if (this.$refs.directBuyModal) {
      //     } else {
      //       console.error('directBuyModal ref is not available');
      //     }
      //   });
      // } else {
      //   this.$router.push({ name: 'cartNew' });
      // }
    },

    //new
    selectColor(color) {
      this.product_form.variants_name = color.name
      this.product_form.variants_ids = color.id

      this.large_image = color.gallery_large

      this.activeImage(this.productDetails.gallery.small.indexOf(color.gallery_small))

      this.product_form.variant_price = color.price
      this.product_form.variant_discount_price = color.discount_price

      this.selectedColor = color?.code;
    },

    //end new
    activeImage(imageIndex) {
      this.current_index = imageIndex;
      this.large_image = this.productDetails.gallery.large[imageIndex];
    },
    setCountDown() {
      this.days = this.productDetails.countdown.days;
      this.hours = this.productDetails.countdown.hours;
      this.minutes = this.productDetails.countdown.mins;
      this.seconds = this.productDetails.countdown.secs;
      this.countDownTimer();
    },
    countDownTimer() {
      setTimeout(() => {
        this.seconds -= 1;
        if (this.seconds <= 0) {
          this.seconds = 59;
          this.minutes -= 1;
          if (this.minutes < 0) {
            this.minutes = 59;
            this.hours -= 1;
            if (this.hours < 0) {
              this.hours = 23;
              this.days -= 1;
            }
          }
        }
        this.countDownTimer();
      }, 1000);
    },
    pageChange(curr_page) {
      this.currentCarousel = curr_page;
    },
    checkCompare() {
      let length = Object.keys(this.compareProducts).length;
      let product = this.productDetails;
      for (let i = 0; i < length; i++) {
        if (
          this.compareProducts[i] &&
          product.id == this.compareProducts[i].id
        ) {
          return true;
        }
      }
      return false;
    },
    removeCompare() {
      if (this.disable) {
        return false;
      }
      this.disable = true;
      let url = this.getUrl(
        "home/remove-compare_product/" + this.productDetails.id
      );
      axios.get(url).then((response) => {
        this.disable = false;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.$store.commit("getRemoveCompare", response.data.product);
          this.$store.dispatch("compareList", response.data.compare_list);
        }
      });
    },
    compare() {
      if (this.disable) {
        return false;
      }
      this.disable = true;
      let url = this.getUrl("home/add-to-compare/" + this.productDetails.id);
      axios.get(url).then((response) => {
        this.disable = false;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.$store.commit("getUserCompare", response.data.product);
          this.$store.dispatch("compareList", response.data.compare_list);
        }
      });
    },
    removeWishlist() {
      if (this.disable) {
        return false;
      }
      this.disable = true;
      let url = this.getUrl(
        "user/remove-wishlist-product/" + this.productDetails.id
      );
      axios.get(url).then((response) => {
        this.disable = false;
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.productDetails.user_wishlist = null;
          this.$store.commit("getRemoveWishlist", response.data.wishlist);
          this.$store.dispatch("wishlists", response.data.totalWishlist);
        }
      });
    },
    addToWishlist() {
      if (this.disable) {
        return false;
      }
      this.disable = true;
      let url = this.getUrl("user/add-to-wishlist/" + this.productDetails.id);
      axios.get(url).then((response) => {
        this.disable = false;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.$store.dispatch("wishlists", response.data.wishlists);
          this.productDetails.user_wishlist = response.data.wishlist;
        }
      });
    },
    stockFind() {
      return (this.firstStock = {
        stock: this.productDetails.product_stock.current_stock,
        sku: this.productDetails.product_stock.sku,
        price: this.productDetails.product_stock.price,
        special_discount_check:
          this.productDetails.product_stock.special_discount_check,
      });
    },
    priceFind() {
      // Use campaign price if available, otherwise use regular price
      let price = this.productDetails.campaign_price?.price || this.productDetails.price;

      if (this.productDetails.wholesale_prices) {
        let whole_sales = this.productDetails.wholesale_prices;

        for (let i = 0; i < whole_sales.length; i++) {
          if (
            whole_sales[i].min_qty <= this.product_form.quantity &&
            whole_sales[i].max_qty >= this.product_form.quantity
          ) {
            price = whole_sales[i].price;
            break;
          }
        }
      }
      return this.priceFormat(price * this.product_form.quantity);
    },
    attributeSelect(el, index, value) {
      let selected_attribute = 0;

      if (this.product_form.attribute_values.length > 0) {
        selected_attribute += this.product_form.attribute_values.length;
      }

      if (this.product_form.color_id) {
        selected_attribute++;
      }

      if (index) {
        this.selected_stock[index] = value;
      }
      if (selected_attribute < this.productDetails.attribute_selector) {
        if (selected_attribute + 1 == this.productDetails.attribute_selector) {
          return this.getAttributes(value);
        }
        return false;
      }
      return this.fetchAttributeStock(value);
    },
    fetchAttributeStock(value) {
      let formData = {
        color_id: this.product_form.color_id,
        product_id: this.productDetails.id,
        variant_ids: this.selected_stock,
        selected_variant: value,
        trx_id: this.carts && this.carts.length > 0 ? this.carts[0].trx_id : "",
      };

      let url = this.getUrl("find/products-variants");
      axios.post(url, formData).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          if (response.data.images) {
            this.large_image = response.data.images["original"];

            for (let i = 0; i < this.productDetails.gallery.small.length; i++) {

              if (
                this.productDetails.gallery.small[i] ===
                response.data.images["image_72x72"]
              ) {
                this.productDetails.gallery[i] =
                  response.data.images["original"];
                this.current_index = i;
              }
            }
          }
          if (response.data.product_stock) {
            this.productDetails.product_stock.current_stock =
              response.data.product_stock.current_stock;
            this.productDetails.product_stock.sku =
              response.data.product_stock.sku;
            this.productDetails.product_stock.price =
              response.data.product_stock.price;
            this.productDetails.product_stock.discount_percentage =
              response.data.product_stock.discount_percentage;
            this.product_form.variants_ids =
              response.data.product_stock.variant_ids;
            this.product_form.variants_name = response.data.product_stock.name;
          } else {
            toastr.error(response.data.msg, this.lang.Error + " !!");
          }
        }
      });
    },
    getAttributes() {
      let formData = {
        color_id: this.product_form.color_id,
        product_id: this.productDetails.id,
        variant_ids: this.selected_stock,
      };

      let url = this.getUrl("find/variants");
      axios.post(url, formData).then((response) => {
        this.allowed_attributes = response.data.variants;
        this.attributes_fetched = true;
      });
    },
    checkDisable(index, value) {

      if(!this.attributes_fetched){

        if(this.productDetails.attribute_values.length > 0 &&
            !this.product_form.color_id &&
            index === this.attributes.length - 1
        ) {
          return true;
        }

      }

      if (this.attributes_fetched) {
        if (
          this.productDetails.product_colors.length > 0 &&
          this.productDetails.attribute_values.length > 0
        ) {
          return (
            this.productDetails.attribute_selector == index + 2 &&
            !this.allowed_attributes.includes(value.id)
          );
        } else {
          return (
            this.productDetails.attribute_selector == index + 1 &&
            !this.allowed_attributes.includes(value.id)
          );
        }
      }
      return false;
    },
    cartPlus() {

      if (
        this.product_form.quantity < this.productDetails.current_stock
      ) {
        this.product_form.quantity++;
      } else {
        toastr.warning(
          this.lang.Only +
          " " +
          this.firstStock.stock +
          " " +
          this.lang.items_available_at_this_time,
          this.lang.Error + " !!"
        );
      }
    },
    cartMinus() {
      if (
        this.product_form.quantity > this.productDetails.minimum_order_quantity
      ) {
        this.product_form.quantity--;
      } else {
        toastr.warning(
          this.lang.please_order_minimum_of +
          " " +
          this.productDetails.minimum_order_quantity +
          " " +
          this.lang.Quantity,
          this.lang.Warning + " !!"
        );
      }
    },
    addToCart(min_qty, buy, is_buy_now) {
      if (is_buy_now == 1 && !this.authUser && this.settings.disable_guest) {
        toastr.error(this.lang.login_first, this.lang.Error + " !!");
        return this.$router.push({ name: "login" });
      }

      if (this.productDetails.has_variant && !this.product_form.variants_ids) {
        return toastr.error(
          this.lang.please_select_all_attributes,
          this.lang.Error + " !!"
        );
      }
      if (is_buy_now == 1) {
        this.product_form.is_buy_now = 1;
      } else {
        this.product_form.is_buy_now = 0;
      }
      let carts = this.carts;
      let url = this.getUrl("user/addToCart");
      axios.post(url, this.product_form).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          toastr.success(response.data.success, this.lang.Success + " !!");
          let carts = response.data.carts;
          if (buy) {
            $("#product").modal("hide");
          }
          this.$store.dispatch("carts", carts);
          this.resetForm();
          this.selectedColor = null
          this.productDetails.product_stock.current_stock =
            this.productDetails.product_stock.current_stock -
            this.product_form.quantity;
          this.product_form.quantity = min_qty;
          if (buy) {
            this.$router.push({ name: "checkout" });
          } else {
            this.added_to_cart = true;
            setTimeout(() => {
              this.added_to_cart = false;
            }, 2000);
          }
        }
      });
    },
    quantityCheck() {
      if (
        this.product_form.quantity != this.firstStock.stock &&
        this.product_form.quantity < this.firstStock.stock
      ) {
        this.product_form.quantity++;
      } else {
        this.product_form.quantity = this.firstStock.stock;
        toastr.warning(
          this,
          lang.only +
          this.stockFind().stock +
          " " +
          this.lang.items_available_at_this_time,
          this.lang.Warning + " !!"
        );
      }

      if (
        this.product_form.quantity > this.productDetails.minimum_order_quantity
      ) {
        this.product_form.quantity--;
      } else {
        this.product_form.quantity = this.productDetails.minimum_order_quantity;
        toastr.warning(
          this.lang.please_order_minimum_of +
          this.productDetails.minimum_order_quantity +
          " " +
          this.lang.Quantity,
          this.lang.Warning + " !!"
        );
      }
    },
    productType() {
      return !(
        this.productDetails.is_catalog == 1 ||
        this.productDetails.is_classified == 1
      );
    },
    submitReview() {
      if (this.product_form.rating == 0) {
        return toastr.error(
          this.lang.choose_a_rating_star_first,
          this.lang.Error + " !!"
        );
      }
      this.review_loading = true;
      this.product_form.product_id = this.productDetails.id;
      this.product_form.paginate = this.paginate;
      let url = this.getUrl("user/product-review-store");
      axios
        .post(url, this.product_form, {
          transformRequest: [
            function (data, headers) {
              return objectToFormData(data);
            },
          ],
        })
        .then((response) => {
          this.review_loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            toastr.success(response.data.success, this.lang.Success + " !!");
            this.resetForm();
            this.reviews = response.data.reviews;
            this.edit = false;
            this.percentages = response.data.percentages;
            this.$store.dispatch("productDetails", this.$route.params.slug);
            this.productDetails.user_review = true;
          }
        })
        .catch((error) => {
          this.review_loading = false;
        });
    },
    fetchReviews() {
      this.activeNav = "delivery";
      if (this.reviews.data.length == 0) {
        let url = this.getUrl("home/product-reviews/" + this.productDetails.id);
        axios
          .get(url)
          .then((response) => {
            if (response.data.error) {
              toastr.error(response.data.error, this.lang.Error + " !!");
            } else {
              this.reviews = response.data.reviews;
              this.percentages = response.data.percentages;
            }
          })
          .catch((error) => {
            toastr.error(this.lang.Oops, this.lang.Error + " !!");
          });
      }
    },
    reviewReply(review_id) {
      this.reply_loading = true;
      this.product_form.review_id = review_id;
      this.product_form.product_id = this.productDetails.id;
      this.product_form.paginate = this.paginate;
      let url = this.getUrl("home/product-reply-store");
      axios
        .post(url, this.product_form)
        .then((response) => {
          this.reply_loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            toastr.success(response.data.success, this.lang.Success + " !!");
            this.resetForm();
            this.reviews.data = response.data.reviews.data;
            this.reviews.next_page_url = response.data.reviews.next_page_url;
            this.reviews.total = response.data.reviews.total;
          }
        })
        .catch((error) => {
          this.reply_loading = false;
        });
    },
    toggleReplyForm(review_id) {
      if (this.reply_form == review_id) {
        this.reply_form = 0;
      } else {
        this.reply_form = review_id;
      }
    },
    showReplies(review_id) {
      if (this.replies == review_id) {
        this.replies = 0;
      } else {
        this.replies = review_id;
      }
    },
    loadReviews() {
      this.paginate++;
      let url = this.getUrl(
        "home/product-reviews/" +
        this.productDetails.id +
        "?page=" +
        this.paginate
      );
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          let reviews = response.data.reviews.data;

          if (reviews.length > 0) {
            for (let i = 0; i < reviews.length; i++) {
              this.reviews.data.push(reviews[i]);
            }
          }
        }
        this.reviews.next_page_url = response.data.reviews.next_page_url;
      });
    },
    reviewLike(id) {
      let data = {
        paginate: this.paginate,
        id: id,
        product_id: this.productDetails.id,
      };
      this.like_loading = true;
      let url = this.getUrl("product/like-review");
      axios
        .post(url, data)
        .then((response) => {
          this.like_loading = false;

          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            if (response.data.success) {
              toastr.success(response.data.success, this.lang.Success + " !!");
            }
            this.reviews.data = response.data.reviews.data;
            this.reviews.next_page_url = response.data.reviews.next_page_url;
            this.reviews.total = response.data.reviews.total;
          }
        })
        .catch((error) => {
          this.like_loading = false;
        });
    },

    
    unLike(id) {
      let data = {
        paginate: this.paginate,
        id: id,
        product_id: this.productDetails.id,
      };
      this.like_loading = true;

      let url = this.getUrl("product/unlike-review");
      axios
        .post(url, data)
        .then((response) => {
          this.like_loading = false;

          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            if (response.data.success) {
              toastr.success(response.data.success, this.lang.Success + " !!");
            }
            this.reviews.data = response.data.reviews.data;
            this.reviews.next_page_url = response.data.reviews.next_page_url;
            this.reviews.total = response.data.reviews.total;
          }
        })
        .catch((error) => {
          this.like_loading = false;
        });
    },
    editReview(review) {
      this.edit = true;
      this.product_form.product_id = this.productDetails.id;
      this.product_form.rating = review.rating;
      this.product_form.title = review.title;
      this.product_form.comment = review.comment;
      let file_name = review.image_link;
      if (file_name) {
        let array = file_name.split("/");
        this.product_form.image_text = array[array.length - 1];
      }
    },
    imageUp(event) {
      this.product_form.image = event.target.files[0];
      document.getElementById("upload-image").innerHTML =
        this.product_form.image.name;
    },
    variantClass(code) {
      return {
        border: "1px solid " + code,
      };
    },
  },
};
</script>

<style>
.toast.toast-error {
  background-color: brown;
}

.toast.toast-success {
  background-color: green;
}

.product-details-card h4 {
  font-size: 18px;
  font-weight: 700;
  line-height: 30px;
  margin-bottom: 0;
}

.tab-content {
  padding: 0px 15px;
  border: none;
  border-radius: 6px;
  background: #EBEBEB;
}

.out-of-stock {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 20px;
  width: 175px;
  padding: 0px 15px;
  border: none;
  border-radius: 6px;
  color: white;
  background: #F91A2F;
}

.tab-content h4 {
  font-size: 15px;

}

.keyfeatures ul {
  list-style: disc;
  line-height: 25px;
}

.keyfeatures ul li::marker {
  font-size: 1.5em;
  /* Adjust the size as needed */
  color: #1E6AAF;
}

.attribute-item {
  height: 20px;
  border: none;
  border-radius: 5px;
  width: 43px;
  cursor: pointer;
}

.attribute-item:hover {
  height: 26px;
}

.attribute-item.active {
  height: 26px;
}

.delivery-item {
  padding: 3px 18px;
  border: none;
  background: white;
  font-size: 14px;
  cursor: pointer;
  border-radius: 5px;
}

.delivery-item p {
  margin-bottom: 0;
}

.delivery-item:hover {
  border: 0.5px solid #1E6AAF
}

.delivery-item.active {
  border: 0.5px solid #1E6AAF;
  color: black !important;
}

.price-section {
  /* max-width: 270px; */
  width: fit-content;
  background: white;
  border: 0.3px solid #78BAF6;
  height: 64px;
  box-shadow: 0px 3px 3px 0px #00000029;
  border-radius: 5px;
  padding-right: 10px;
}

.price-section .space {
  background: #1E6AAF;
  padding: 20px;
}

.price-section h4 {
  margin-bottom: 0;
  font-size: 18px;
  line-height: 28.5px;
}

.price-section p {
  margin-bottom: 0;
  font-size: 10px;
  font-style: italic;
  line-height: 14px;
}

.current-price {
  color: #1E6AAF;
}

.quantity span {
  font-size: 18px;
  font-weight: 500;
  line-height: 21.33px;
  cursor: pointer;
}

.product-details-card-button {
  width: 135px;
  height: 30px;
  border-radius: 5px;
  font-size: 15px;
  line-height: 26px;
  border: none;
  background: transparent;
}

button.product-details-card-button.add-cart {
  border: 0.5px solid #707070
}

button.product-details-card-button.buy-now {
  background: #1E6AAF;
  color: white;
}

@media screen and (max-width: 430px) {
  /* .slick-track {
    display: flex !important;
    flex-direction: column !important;
    width: 70px !important;
    margin-left: 10px !important;


  }

  .slick-slide {
    width: 100% !important;
  } */

  .product-image img {
    height: 265px !important;
    width: 285px !important;
  }
}

/* Thumbnail active state styling */
.thumb-item {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  border-radius: 4px;
  overflow: hidden;
}

.thumb-item.active {
  border-color: #1E6AAF;
  box-shadow: 0 0 8px rgba(30, 106, 175, 0.5);
}

.thumb-item:hover {
  border-color: #1E6AAF;
  opacity: 0.8;
}

.thumb-item .thumbnail-img img {
  width: 100%;
  height: auto;
  display: block;
}

/* Mobile thumbnail improvements */
@media screen and (max-width: 768px) {
  .thumb-item {
    margin: 0 2px;
  }

  .thumb-item.active {
    border-width: 3px;
    transform: scale(1.05);
  }
}
</style>
