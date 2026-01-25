<template>
    <div>

        <div class="product-card" :style="{ maxWidth: maxWidth }"  v-if="product?.slug != undefined">
            <img :src="product.image_190x230" isloading="lazy" @click="openModal(product.slug)" :alt="product.product_name" class="img-fluid transition-transform hover-zoom"
            >
            <p class="product-card-title">
                <router-link :to="{ name: 'productDetailsNew', params: { slug: product?.slug } }">
                    {{ product?.product_name }}
                </router-link>
            </p>


            <div class="d-flex justify-content-center price" v-if="product.special_discount_check > 0">
                <span class="price original">{{ priceFormat(product?.discount_percentage) }}</span>
                <span class="line">|</span>
                <span class="price discount">{{ priceFormat(product?.price) }}</span>
            </div>
            <div class="d-flex justify-content-center price" v-else>
                <span class="price original">{{ priceFormat(product?.price) }}</span>
            </div>

            <div class="product-offer" v-if="product.special_discount_check > 0">

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
    padding: 11px;
    min-width: 258px;
    min-width: 258px;
}

.product-card-title {
    width: 100% !important;
    /* padding-right: 21px;/ */
}

.product-card img {
    height: 165px;
    cursor: pointer;
}

.product-card p {
    margin-top: 20px;
    font-size: 16px;
    font-weight: 500;
    /* width: 246px; */
    height: 45px;
    overflow: hidden;
}

.product-card .price {
    font-size: 16px;
    font-weight: 500;
}

.product-card .line {
    color: #707070;
    margin: 0px 8px;
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
    gap: 8px;
    margin-top: 10px;
    flex-wrap: nowrap;
    width: 100%;
}

button.product-button {
    padding: 6px 16px;
    border-radius: 21px;
    font-size: 13px;
    background: none;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
    max-width: 48%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

button.product-button .btn-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    width: 100%;
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
    top: 0;
    left: 0;

}

.product-offer .product-offer-text {
    background: #1BADEB;
    color: white;
    padding: 3px 10px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 3px;
    height: auto;
}

.hover-zoom {
  transition: transform 0.3s ease-in-out;
}

.hover-zoom:hover {
  transform: scale(1.1); 
}


@media screen and (max-width: 1024px) {
    .featured_product_items{
        display: grid !important;
        grid-template-columns: repeat(4, 1fr);
    }

    .product-card {
        max-width: 230px !important;
        min-width: 230px !important;
        height: 340px !important;
    }

    /* .product-card-title {
        width: 230px;
        height: 48px;
        overflow-y: auto;
    } */

    .product-card p{
        margin-top: 20px;
        font-size: 15px;
        font-weight: 400;
        width: 100% !important;
        height: 44px;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 14px;
        font-weight: 500;
        display: flex !important ;
        justify-content: center !important;
    }

    .product-button-group {
        gap: 6px;
    }

    button.product-button {
        padding: 5px 12px;
        font-size: 12px;
    }

}
@media screen and (max-width: 768px) {
    .featured_product_items{
        display: grid !important;
        grid-template-columns: repeat(3, 1fr);
    }

    .product-card {
        max-width: 230px !important;
        min-width: 230px !important;
        height: 340px !important;
    }

    .product-card p{
        margin-top: 20px;
        font-size: 13px;
        font-weight: 400;
        width: 100% !important;
        height: 44px;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 13px;
        font-weight: 500;
        display: flex !important ;
        justify-content: center !important;
    }

    .product-button-group {
        gap: 5px;
    }

    button.product-button {
        padding: 4px 10px;
        font-size: 11px;
    }

    .product-card-title {
        width: 100% !important;
    }
} 

@media screen and (max-width: 435px) {
    .featured_product_items{
        display: grid !important;
        grid-template-columns: repeat(2, 1fr);
    }

    .product-card {
        max-width: 198px !important;
        min-width: 198px !important;
        height: 340px !important;
    }

    .product-card p{
        margin-top: 20px;
        font-size: 11px;
        font-weight: 400;
        width: 100% !important;
        height: 44px;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 11px;
        font-weight: 500;
        display: flex !important ;
        justify-content: center !important;
    }

    .product-button-group {
        gap: 4px;
    }

    button.product-button {
        padding: 4px 8px;
        font-size: 10px;
    }

    .product-card-title {
        width: 100% !important;
    }
    .product-offer{
    display: block;
    padding: 3px 10px;
    font-weight: 500;
    border-radius: 3px;
    height: auto;
    }

    .product-offer-text{
        font-size: 11px !important;
    }

}



/* @media screen and (max-width: 414px) {
    .product-card {
        max-width: 190px !important;
    }
}   */

@media screen and (max-width: 390px) {
    .featured_product_items{
        display: grid !important;
        grid-template-columns: repeat(2, 1fr);
    }

    .product-card {
        max-width: 174px !important;
        min-width: 174px !important;
        height: 340px !important;
    }

    .product-card p{
        margin-top: 20px;
        font-size: 11px;
        font-weight: 400;
        width: 100% !important;
        height: 44px;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 11px;
        font-weight: 500;
        display: flex !important ;
        justify-content: center !important;
    }

    .product-button-group {
        gap: 4px;
    }

    button.product-button {
        padding: 3px 6px;
        font-size: 9px;
    }

    .product-card-title {
        width: 100% !important;
    }
}

@media screen and (max-width: 360px) {

    .featured_product_items{
        display: grid !important;
        grid-template-columns: repeat(2, 1fr);
    }

    .product-card {
        max-width: 145px !important;
        min-width: 145px !important;
        height: 340px !important;
    }

    .product-card p{
        margin-top: 20px;
        font-size: 10px;
        font-weight: 400;
        width: 100% !important;
        height: 44px;
        overflow: hidden;
    }

    .product-card .price {
        font-size: 10px;
        font-weight: 500;
        display: flex !important ;
        justify-content: center !important;
    }

    .product-button-group {
        gap: 3px;
    }

    button.product-button {
        padding: 3px 5px;
        font-size: 8px;
    }

}

@media only screen and (min-width: 300px) and (max-width: 359px) {
.featured_product_items{
    display: grid !important;
    grid-template-columns: repeat(2, 1fr);
}
.product-card {
    max-width: 100% !important;
    min-width: 100% !important;
    height: 340px !important;
}
.product-card p{
    margin-top: 20px;
    font-size: 10px;
    font-weight: 400;
    width: 100% !important;
    height: 44px;
    overflow: hidden;
}
.product-card .price {
    font-size: 10px;
    font-weight: 500;
    display: flex !important ;
    justify-content: center !important;
}

.product-button-group {
    gap: 2px;
}

button.product-button {
    padding: 2px 4px;
    font-size: 7px;
}
}

</style>
