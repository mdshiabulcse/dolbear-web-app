<template>
  <div class="sg-page-content">
    <section class="product-details">
      <div class="container">
        <details-view :productDetails="productDetails"></details-view>
      </div><!-- /.container -->
    </section><!-- /.product-details -->
    <section class="products-section"
             v-if="productDetails.related_products && productDetails.related_products.length > 0">
      <div class="container">
        <div class="title justify-content-between">
          <h1>{{ lang.related_products }}</h1>
        </div>
        <product :products="productDetails.related_products"  :number="8" :grid_class="width > 769 ? 'grid-6' : 'grid-2'"></product>
      </div><!-- /.container -->
    </section>
  </div>
</template>

<script>

import product from "./product";
import detailsView from "../partials/details-view.vue";

export default {
  name: "product-details",
  components: {product, detailsView},
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
