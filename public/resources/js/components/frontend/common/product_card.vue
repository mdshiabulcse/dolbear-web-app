<template>
    <div>

        <div class="product-card" :style="{ maxWidth: maxWidth }" v-if="product?.slug != undefined">
            <img :src="product.image_190x230" isloading="lazy" @click="openModal(product.slug)" :alt="product.product_name">
            <p class="product-card-title">
                <router-link :to="{ name: 'productDetailsNew', params: { slug: product?.slug } }">
                    {{ product?.product_name }}
                </router-link>
            </p>


            <div class="d-flex justify-content-center price">
                <span class="price original">{{ priceFormat(product?.price) }}</span>
                <span class="line">|</span>
                <span class="price discount">৳2,800</span>
            </div>
            <div class="product-offer" v-if="product.special_discount_check > 0">

                <p>{{ product.special_discount_type == "flat" ? priceFormat(product.special_discount_check) + " " +
                    lang.off : product.special_discount_check + "% " + lang.off }}</p>
            </div>
            <div class="d-flex justify-content-center gap-2">
                <button class="product-button outline" @click="addToCart()">Add List</button>
                <button class="product-button primary" @click="openDirectBuyModal()">Buy Now</button>
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





            this.$refs.productModal.showModal()
        },

        addToCart() {

          if (this.product.has_variant && !this.product_form.variants_ids) {
            toastr.error(
                this.lang.please_select_all_attributes,
                this.lang.Error + " !!"
            );

            console.log(this.product.slug)

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

        openDirectBuyModal() {

          this.addToCart()

          if(!this.authUser){
            this.$refs.directBuyModal.showOrderModal()
          }else{
            this.$router.push({name: 'cartNew'});
          }
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
}

.product-card-title {
    width: 254px;
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

button.product-button {
    padding: 4px 26px;
    border: 2px solid #1BADEB;
    border-radius: 21px;
    font-size: 13px;
    background: none;
    font-weight: 500;
}

button.product-button:hover {
    background: #040404 !important;
    color: white;
    border: none;
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

.product-offer p {
    background: #1BADEB;
    color: white;
    padding: 3px 10px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 3px;
    height: auto;
}
@media screen and (max-width: 1024px) {
    .product-card {
        max-width: 255px !important;
        height: 340px !important;
    }
    .product-card-title {
        width: 230px;
        height: 48px;
        overflow-y: auto;
    }
}
@media screen and (max-width: 768px) {
    .product-card {
        max-width: 228px !important;
        height: 340px !important;
    }
    .product-card-title {
        width: 211px;
        height: 48px;
        overflow-y: auto;
    }
    button.product-button {
        padding: 3px 21px;
    }
} 

@media screen and (max-width: 435px) {
    .product-card {
        max-width: 197px !important;
        height: 340px !important;
    }

    .product-card-title {
        width: 173px;
        height: 48px;
        overflow-y: auto;
    }

    button.product-button {
        padding: 3px 8px;
        border: 1px solid #1BADEB;
        border-radius: 18px;
        font-size: 13px;
    }
}



@media screen and (max-width: 414px) {
    .product-card {
        max-width: 190px !important;
    }
}  

@media screen and (max-width: 390px) {
    .product-card {
        max-width: 173px !important;
    }
    .product-card-title {
        width: 153px;
    }
}

@media screen and (max-width: 360px) {
    .product-card {
        max-width: 166px !important;
    }
    button.product-button {
        padding: 3px 6px;
    }
 
}
</style>
