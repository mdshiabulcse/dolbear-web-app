<template>
    <div>

        <div class="product-card" v-if="product?.slug != undefined">
            <!-- Campaign Badge -->
            <div class="product-badge" v-if="product.discount_info && product.discount_info.badge_text"
                 :style="{ backgroundColor: product.discount_info.badge_color || '#ff0000' }">
                {{ product.discount_info.badge_text }}
            </div>

            <img :src="product.image_190x230" isloading="lazy" @click="openModal(product.slug)" :alt="product.product_name" class="img-fluid transition-transform hover-zoom"
            >
            <p class="product-card-title">
                <router-link :to="{ name: 'productDetailsNew', params: { slug: product?.slug } }">
                    {{ product?.product_name }}
                </router-link>
            </p>

            <!-- Campaign Price Display -->
            <div class="d-flex justify-content-center price" v-if="product.campaign_price && product.campaign_price < product.price">
                <span class="price original">{{ priceFormat(product?.price) }}</span>
                <span class="line">|</span>
                <span class="price discount">{{ priceFormat(product?.campaign_price) }}</span>
            </div>
            <!-- Special Discount Price Display -->
            <div class="d-flex justify-content-center price" v-else-if="product.special_discount_check > 0">
                <span class="price original">{{ priceFormat(product?.discount_percentage) }}</span>
                <span class="line">|</span>
                <span class="price discount">{{ priceFormat(product?.price) }}</span>
            </div>
            <!-- Regular Price Display -->
            <div class="d-flex justify-content-center price" v-else>
                <span class="price original">{{ priceFormat(product?.price) }}</span>
            </div>

            <!-- Campaign Discount Badge -->
            <div class="product-offer" v-if="product.discount_info && product.discount_info.formatted_discount">
                <div class="product-offer-text">
                    {{ product.discount_info.formatted_discount }} {{ lang.off || 'OFF' }}
                </div>
            </div>
            <!-- Special Discount Badge -->
            <div class="product-offer" v-else-if="product.special_discount_check > 0">
                <div  class="product-offer-text">{{ product.special_discount_type == "flat" ? priceFormat(product.special_discount_check) + " " +
                    lang.off : product.special_discount_check + "% " + lang.off }}</div>
            </div>
            <div class="product-button-group">
                <button class="product-button outline" @click="addToCart()">
                    <span class="btn-text">Add to Cart</span>
                </button>
                <button class="product-button primary" @click="openDirectBuyModal()">
                    <span class="btn-text">Buy Now</span>
                </button>
            </div>
        </div>

        <h4 v-else>{{ lang.no_product_found }}</h4>

        <Product_quck_modal ref="productModal" :product="productData" :isloading="isloading" />

        <direct_buy_modal ref="directBuyModal" />

    </div>
</template>

<script>
import { set } from 'lodash';
import Product_quck_modal from './product_quck_modal.vue';
import direct_buy_modal from './direct_buy_modal.vue';


export default {
    data() {
        return {
            productData: {},
            isloading: false,
        }
    },
    components: {
        Product_quck_modal,
        direct_buy_modal
    },
    props: {
        product: {
            type: Object,
            required: true,
        },
        maxWidth: {
            type: String,
            default: '258px',
        },
        minWidth: {
        type: String,
        default: '258px', 
    }
        
    },
    computed: {
        carts() {
            let carts = this.$store.getters.getCarts;
            if (carts && carts[0]) {
                this.product_form.trx_id = carts[0].trx_id;
            }
            return carts;
          }
    },
    
    methods: {
        openModal(slug) {
            this.isloading = true
            let set_params = {
                slug: slug,
                referral_code: '',
                trx_id: '',
            }

            this.$store.dispatch('productView', this.slug);
            if (!this.productDetails) {
                this.$store.dispatch('productDetails', set_params);
            }

            setTimeout(() => {
                let data = this.$store.getters.getProductDetails;
                this.productData = data.find(p => p.slug == slug).product
                this.isloading = false
            }, 2000);





          toastr.warning("Select variant", "Warning !!");
          this.$refs.productModal.showModal()
        },

        addToCart() {

          if (this.product.has_variant && !this.product_form.variants_ids) {
            toastr.error(
                this.lang.please_select_all_attributes,
                this.lang.Error + " !!"
            );

            this.openModal(this.product.slug)

            return
          }

            this.product_form.id = this.product.id;
            
            let carts = this.carts;
            let url = this.getUrl("user/addToCart");

            axios.post(url, this.product_form).then((response) => {
                if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
                } else {
                toastr.success(response.data.success, this.lang.Success + " !!");
                let carts = response.data.carts;
                
                    this.$store.dispatch("carts", carts);

                
                
                }
            });
        },

        addToCartDirect() {

          if (this.product.has_variant && !this.product_form.variants_ids) {

            this.openModal(this.product.slug)

            return
          }

            this.product_form.id = this.product.id;

            let carts = this.carts;
            let url = this.getUrl("user/addToCart");

            axios.post(url, this.product_form).then((response) => {
                if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
                } else {
                // toastr.success(response.data.success, this.lang.Success + " !!");
                let carts = response.data.carts;

                    this.$store.dispatch("carts", carts);

                }
            });
        },

        async openDirectBuyModal() {

          await this.addToCartDirect()

          if(this.product.has_variant && !this.product_form.variants_ids)
          {
            return
          }

          if(!this.authUser){
            this.$refs.productModal.hideModal()
            this.$refs.directBuyModal.showOrderModal()
          }else{
            this.$router.push({name: 'cartNew'});
          }
        }
    },
  
    watch: {
        product() {
        }
    },

}
</script>


<style>
.product-card {
    position: relative;
    height: 340px;
    border-radius: 15px;
    background: #FFFFFF;
    text-align: center;
    padding: 10px;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
    background: #ff0000;
    color: white;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.product-card-title {
    width: 100% !important;
}

.product-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    cursor: pointer;
    border-radius: 10px;
    margin-bottom: 8px;
}

.product-card p {
    margin: 0 0 6px 0;
    font-size: 14px;
    font-weight: 500;
    height: 38px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.3;
}

.product-card .price {
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 6px;
}

.product-card .line {
    color: #707070;
    margin: 0px 6px;
}

.discount {
    font-weight: bold;
    font-size: 1.1rem;
    position: relative;
}

.product-card .discount::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 2px;
    background-color: #e63946;
    transform: translateY(-50%);
}

.product-button-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: auto;
    flex-wrap: nowrap;
    width: 100%;
}

button.product-button {
    padding: 8px 6px;
    border-radius: 20px;
    font-size: 11px;
    background: none;
    font-weight: 500;
    white-space: normal;
    word-wrap: break-word;
    overflow: visible;
    flex: 1;
    max-width: 48%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    min-height: 32px;
    line-height: 1.2;
    text-align: center;
}

button.product-button .btn-text {
    white-space: normal;
    word-wrap: break-word;
    display: block;
    width: 100%;
    line-height: 1.2;
}

button.product-button:hover {
    background: #040404 !important;
    color: white;
}

.primary {
    background: #1BADEB !important;
    border: none;
    color: white;
}

.product-offer {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 1;
}

.product-offer .product-offer-text {
    background: #1BADEB;
    color: white;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 4px;
    height: auto;
}

.hover-zoom {
    transition: transform 0.3s ease-in-out;
}

.hover-zoom:hover {
    transform: scale(1.05);
}



/* Large Tablet/Desktop - 1199px to 1024px */
@media screen and (min-width: 1025px) and (max-width: 1199px) {
    .product-card {
        padding: 9px;
        height: 320px;
    }

    .product-card img {
        height: 170px;
        margin-bottom: 7px;
    }

    .product-card p {
        font-size: 13px;
        height: 36px;
        margin-bottom: 5px;
    }

    .product-card .price {
        font-size: 13px;
        margin-bottom: 5px;
    }

    .product-button-group {
        gap: 5px;
    }

    button.product-button {
        padding: 7px 5px;
        font-size: 10px;
        min-height: 30px;
    }

    .product-offer-text {
        font-size: 10px;
        padding: 2px 6px;
    }
}

/* Tablet - 1024px and below */
@media screen and (max-width: 1024px) {
    .product-card {
        padding: 8px;
        height: 300px;
    }

    .product-card img {
        height: 155px;
        margin-bottom: 6px;
    }

    .product-card p {
        font-size: 12px;
        height: 34px;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .product-card .price {
        font-size: 12px;
        margin-bottom: 4px;
    }

    .product-button-group {
        gap: 4px;
    }

    button.product-button {
        padding: 6px 4px;
        font-size: 9px;
        min-height: 28px;
    }

    .product-offer-text {
        font-size: 9px;
        padding: 2px 5px;
    }
}

/* Tablet - 999px and below */
@media screen and (max-width: 999px) {
    .product-card {
        padding: 7px;
        height: 290px;
    }

    .product-card img {
        height: 150px;
        margin-bottom: 5px;
    }

    .product-card p {
        font-size: 12px;
        height: 32px;
        margin-bottom: 3px;
        line-height: 1.2;
    }

    .product-card .price {
        font-size: 12px;
        margin-bottom: 3px;
    }

    .product-button-group {
        gap: 4px;
        margin-top: auto;
    }

    button.product-button {
        padding: 5px 4px;
        font-size: 9px;
        min-height: 27px;
    }

    .product-offer-text {
        font-size: 9px;
        padding: 2px 5px;
    }
}

/* Small tablet - 768px */
@media screen and (max-width: 768px) {
    .product-card {
        padding: 6px;
        height: 270px;
    }

    .product-card img {
        height: 135px;
        margin-bottom: 4px;
    }

    .product-card p {
        font-size: 11px;
        height: 30px;
        margin-bottom: 3px;
        line-height: 1.2;
    }

    .product-card .price {
        font-size: 11px;
        margin-bottom: 3px;
    }

    .product-button-group {
        gap: 3px;
        margin-top: auto;
    }

    button.product-button {
        padding: 5px 3px;
        font-size: 8px;
        min-height: 26px;
        border-radius: 15px;
    }

    .product-offer-text {
        font-size: 8px;
        padding: 2px 4px;
    }
}

/* Mobile - 576px and below */
@media screen and (max-width: 576px) {
    .product-card {
        padding: 8px;
        height: 100%;
        min-height: 260px;
        display: flex;
        flex-direction: column;
    }

    .product-card img {
        width: 100%;
        height: auto;
        max-height: 140px;
        object-fit: contain;
        margin-bottom: 8px;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    .product-card p {
        font-size: 12px;
        height: auto;
        min-height: 34px;
        margin-bottom: 6px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 13px;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .product-button-group {
        gap: 4px;
        margin-top: auto;
    }

    button.product-button {
        padding: 8px 6px;
        font-size: 10px;
        min-height: 32px;
        border-radius: 8px;
        font-weight: 600;
    }

    .product-offer-text {
        font-size: 9px;
        padding: 3px 6px;
    }
}

/* Small mobile - 430px */
@media screen and (max-width: 430px) {
    .product-card {
        padding: 6px;
        min-height: 240px;
    }

    .product-card img {
        max-height: 120px;
        margin-bottom: 6px;
    }

    .product-card p {
        font-size: 11px;
        min-height: 30px;
        margin-bottom: 5px;
    }

    .product-card .price {
        font-size: 12px;
        margin-bottom: 5px;
    }

    .product-button-group {
        gap: 4px;
    }

    button.product-button {
        padding: 7px 5px;
        font-size: 9px;
        min-height: 30px;
    }

    .product-offer-text {
        font-size: 8px;
        padding: 2px 5px;
    }
}

/* Extra small mobile - 360px */
@media screen and (max-width: 360px) {
    .product-card {
        padding: 5px;
        min-height: 220px;
    }

    .product-card img {
        max-height: 110px;
        margin-bottom: 5px;
    }

    .product-card p {
        font-size: 10px;
        min-height: 28px;
        margin-bottom: 4px;
    }

    .product-card .price {
        font-size: 11px;
        margin-bottom: 4px;
    }

    .product-button-group {
        gap: 3px;
    }

    button.product-button {
        padding: 6px 4px;
        font-size: 9px;
        min-height: 28px;
    }

    .product-offer-text {
        font-size: 8px;
        padding: 2px 4px;
    }
}

</style>
