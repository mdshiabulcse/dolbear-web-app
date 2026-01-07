<template>
  <div class="container">
    <Breadcrumb :slug="productDetails?.category_title" />
    <product_details_card :productDetails="productDetails"></product_details_card>

    <Product_specification :productDetails="productDetails"></product_specification>

    <h4 class="mb-2 mt-2">Related Products</h4>
    <div class="d-flex flex-wrap gap-2">
      <div v-for="product in productDetails?.related_products" :key="product.id">
        <product_card :product="product"></product_card>
      </div>
    </div>

  </div>
</template>

<script>

import product from "./product";
import product_details_card from "../common/product_details_card.vue";
import Breadcrumb from "../common/breadcrumb.vue";
import Product_specification from "../common/product_specification.vue";
import Product_card from "../common/product_card.vue";





export default {
  name: "product-details",
  components: { product, product_details_card, Breadcrumb, Product_specification, Product_card },
  data() {
    return {
      hours: 0,
      minutes: 0,
      seconds: 0,
      slug: {
        product_slug: this.$route.params.slug
      },
      width: window.innerWidth,

    }

  },
  watch: {
    $route() {
      let set_params = {
        slug: this.$route.params.slug,
        referral_code: this.$route.query.referral_code,
        trx_id: this.carts && this.carts.length > 0 ? this.carts[0].trx_id : '',
      }
      if (!this.productDetails) {

        this.$store.dispatch('productDetails', set_params);
      }
    }
  },
  mounted() {
    window.addEventListener('resize', this.updateDimensions);
    let set_params = {
      slug: this.$route.params.slug,
      referral_code: this.$route.query.referral_code,
      trx_id: this.carts && this.carts.length > 0 ? this.carts[0].trx_id : '',
    }

    this.$store.dispatch('productView', this.slug);
    if (!this.productDetails) {
      this.$store.dispatch('productDetails', set_params);
    }

  },
  computed: {
    productDetails() {
      let products = this.$store.getters.getProductDetails;
      for (let i = 0; i < products.length; i++) {
        if (products[i].slug == this.$route.params.slug) {
          if (products[i].product.status != 'published') {
            toastr.warning(products[i].product.product_name + this.lang.is_unavailable_at_this_moment, this.lang.Warning + ' !!');

            return this.$router.go(-1);
          }
          return products[i].product;
        }
      }

      return false;
    },
    carts() {
      return this.$store.getters.getCarts;
    },
  },
  methods: {
    updateDimensions() {
      this.width = window.innerWidth;
    },
    countDownTimer() {
      const now = new Date().getTime();
      // let startOfDay = new Date(now - (now % 86400000)).toUTCString();
      let end_time = new Date(now - (now % 86400000) + 86400000); //2022-12-25 09:33:00
      let final_end_time = '';
      final_end_time += end_time.getFullYear() + '-';
      final_end_time += end_time.getMonth() + 1 + '-';
      final_end_time += end_time.getDate() - 1 + ' ';
      final_end_time += '23:';
      final_end_time += '59:';
      final_end_time += '59';
      return final_end_time;
    },

  },
  beforeDestroy() {
    window.removeEventListener('resize', this.updateDimensions);
  },
}
</script>
