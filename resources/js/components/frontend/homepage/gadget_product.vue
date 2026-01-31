<template>
  <section class="products-section category-products ajax_scroll bg-white">
    <div class="container bg-white" v-if="gadget_product && lengthCounter(products) > 0">
      <!-- <div class="title justify-content-between" :class="{ 'title-bg' : addons.includes('ishopet') }">
				<h1>{{ category }}</h1>
				<router-link :to="{ name: 'product.by.gadget', params: { slug: gadget_product.slug } }">{{ lang.more_products }} <span class="icon mdi mdi-name mdi-arrow-right"></span></router-link>
			</div> -->

      <div class="custom-padding "></div>

      <h1 class="text-center custom-padding-bottom ">Products</h1>

      <div class="container ">
        <div class="row justify-content-center">
          <div class="col-auto">
            <button type="button" class="btn custom-btn">New Arrivals</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Best Sellers</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Bundle Deals</button>
          </div>
        </div>
      </div>

      <div class="row">
        <div
          class="col-md-3"
          v-if="category_sec_banner_url && window.width >= 1200"
        >
          <div class="products">
            <div class="category-product">
              <div class="thumb" v-if="category_sec_banner_url">
                <a
                  v-if="urlCheck(category_sec_banner_url)"
                  :href="category_sec_banner_url"
                  class="add-banner"
                >
                  <img
                    :src="category_sec_banner"
                    :alt="category_sec_banner"
                    class="img-fluid"
                  />
                </a>
                <router-link
                  v-else
                  :to="category_sec_banner_url"
                  class="add-banner"
                >
                  <img
                    :src="category_sec_banner"
                    :alt="category_sec_banner_url"
                    class="img-fluid"
                  />
                </router-link>
              </div>
            </div>
          </div>
        </div>
        <div :class="[window.width < 1200 ? 'col-lg-12' : 'col-lg-8']">
          <product
            :products="products"
            :grid_class="
              window.width < 1200 ? 'grid-4 grid-show' : 'grid-4 product-space'
            "
          ></product>
        </div>
      </div>
    </div>
    <div class="container" v-else-if="show_shimmer">
      <div class="title justify-content-between">
        <shimmer :height="45"></shimmer>
      </div>
      <div class="row">
        <div class="col-md-4" v-if="window.width >= 1200">
          <div class="products">
            <div class="category-product">
              <div class="thumb">
                <a href="#"><shimmer :height="754"></shimmer></a>
              </div>
            </div>
          </div>
        </div>
        <div :class="[window.width < 1200 ? 'col-lg-12' : 'col-lg-8']">
          <ul
            class="products"
            :class="
              window.width < 1200 ? 'grid-4 grid-show' : 'grid-4 product-space'
            "
          >
            <li v-for="(product, index) in 8" :key="index">
              <div class="sg-product">
                <a href="javaScript:void(0)">
                  <shimmer :height="364"></shimmer>
                </a>
              </div>
              <!-- /.sg-product -->
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import product from "../pages/product";
import shimmer from "../partials/shimmer";

export default {
  name: "gadget_product",
  data() {
    return {
      window: {
        width: 0,
        height: 0,
      },
      show_shimmer: true,
    };
  },
  components: {
    product,
    shimmer,
  },

  props: ["gadget_product", "category_sec_banner", "category_sec_banner_url"],

  computed: {
    category() {
      if (this.gadget_product) return this.gadget_product.name;
    },
    products() {
      if (
        this.gadget_product &&
        this.gadget_product.products &&
        this.gadget_product.products.length > 0
      ) {
        return this.gadget_product.products;
      } else {
        return [];
      }
    },
  },
  created() {
    window.addEventListener("resize", this.handleResize);
    this.handleResize();
  },
  destroyed() {
    window.removeEventListener("resize", this.handleResize);
  },
  mounted() {
    this.checkHomeComponent("category_section");
  },
  watch: {
    homeResponse() {
      this.checkHomeComponent("category_section");
    },
  },
  methods: {
    handleResize() {
      this.window.width = window.innerWidth;
      this.window.height = window.innerHeight;
    },
    checkHomeComponent(component_name) {
      let component = this.homeResponse.find((data) => data == component_name);

      if (component) {
        return (this.show_shimmer = false);
      }
    },
  },
};
</script>

<style scoped>
.custom-padding {
  padding: 5% 0;
}

.custom-padding-bottom {
  padding-bottom: 5%;
}

.custom-btn {
		color: black;
		background-color: white;
		padding: 15px;
		border-radius: 6px;
		font-size: large;
    }
    .custom-btn:hover {
      background-color: #f8f9fa; /* Change background color on hover */
    }
	.bg-white {
		background-color:#FFFFFF;
	}
</style>
