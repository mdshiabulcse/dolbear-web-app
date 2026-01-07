<template>
  <section
    class="products-section item-space-rmv bg-color-products"
    v-if="lengthCounter(products) > 0"
  >
    <div class="container" v-if="products[0] != 'id'">
      <!-- <div class="title justify-content-between" :class="{ 'title-bg title-btm-space' : addons.includes('ishopet') }">
				<h1> {{ lang.best_selling_product }}ijadkasf</h1>
				<a :href="getUrl('best-selling/products')" @click.prevent="navigator">{{ lang.more_products }} <span class="icon mdi mdi-name mdi-arrow-right"></span></a>
			</div> -->

      <div class="custom-padding"></div>

      <h1 class="text-center custom-padding-bottom">Products</h1>

      <div class="container">
        <div class="row justify-content-center">
          <div class="col-auto">
            <button type="button" class="btn custom-btn">New Arrivalsoooo</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Best Sellers</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Bundle Deals</button>
          </div>
        </div>
      </div>

      <div class="custom-padding-bottom"></div>

      <productCarousel
        :products="products"
        :number="12"
        :grid_class="'grid-4'"
      ></productCarousel>
    </div>

    <!-- Company Policy -->
    <companyPolicySection></companyPolicySection>

   <!-- Image Box -->
    <div>
      <section
        class="offers-section"
      >
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <a
                href="/"
                aria-current="page"
                class="add-banner router-link-exact-active router-link-active"
                id="_0"
                ><img
                  src="https://yoori.spagreen.net/public/images/20240218163718image_620x320-394.png"
                  alt="https://yoori.spagreen.net/public/images/20240218163717image_620x320-413.png_0"
                  class="img-fluid"
              /></a>
            </div>
            <div class="col-md-6">
              <a
                href="/"
                aria-current="page"
                class="add-banner router-link-exact-active router-link-active"
                id="_1"
                ><img
                  src="https://yoori.spagreen.net/public/images/20240218163718image_620x320-258.png"
                  alt="https://yoori.spagreen.net/public/images/20240218163717image_620x320-40.png_1"
                  class="img-fluid"
              /></a>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Products again -->
    <div class="container" v-if="products[0] != 'id'">

      <div class="custom-padding"></div>

      <h1 class="text-center custom-padding-bottom">Products Again</h1>

      <productCarousel
        :products="products"
        :number="12"
        :grid_class="'grid-4'"
      ></productCarousel>
      <productCarousel
        :products="products"
        :number="12"
        :grid_class="'grid-4'"
      ></productCarousel>
      <productCarousel
        :products="products"
        :number="12"
        :grid_class="'grid-4'"
      ></productCarousel>
    
    </div>

    <!-- Video section -->
    <div class="video-container">
      <video autoplay loop muted playsinline class="embed-responsive-item">
        <source src="/video/video.mp4" type="video/mp4" />
        Your browser does not support the video tag.
      </video>
      <div class="shade"></div>
      <div class="button-container">
        <button class="btn btn-primary btn-style">Play Video</button>
      </div>
    </div>

    <!-- <div style="height: 100px">
      <h1>recomendation</h1>
    </div> -->

    <!-- Offer section -->
    <div class="row get-back-section">
      <div class="col-md-6 text-end">
        <p class="offer-section">
          <span class="text-style">Get Back</span>
          <span class="text-style-second">15% OFF</span>
          <span class="text-style-third">On Your First Order{{ products[0] }}</span>
        </p>
      </div>
      <div class="col-md-6 align-center">
        <input class="input-extra-style" placeholder="Enter Your Email" />
        <button class="button-17 button-extra-style" role="button">Send</button>
      </div>
    </div>

    <div style="height: 180px"></div>
  </section>
  <!-- /.section -->
  <!-- <section class="products-section bg-white selling" v-else-if="show_shimmer">
		<div class="container">
			<ul class="products grid-4">
				<li v-for="(product, index) in 6" :key="index">
					<div class="sg-product">
						<a href="javascript:void(0)">
							<shimmer :height="364"></shimmer>
						</a> </div>
				</li>
			</ul>
		</div>
	</section> -->
  <!-- HTML !-->
</template>
<script>
import productCarousel from "../pages/product-carousel";
import companyPolicySection from "./company_policy_section.vue";
import shimmer from "../partials/shimmer";

export default {
  name: "best_selling_product",
  components: {
    productCarousel,
    shimmer,
    companyPolicySection,
  },
  props: ["best_selling_product"],
  data() {
    return {
      show_shimmer: true,
    };
  },
  mounted() {
    
    this.checkHomeComponent("best_selling_product");
    this.checkShopComponent("best_selling_product");
  },
  
  watch: {
    homeResponse() {
      this.checkHomeComponent("best_selling_product");
    },
    shopResponse() {
      this.checkShopComponent("best_selling_product");
    },
  },
  computed: {
    products() {
      if (this.best_selling_product && this.best_selling_product.length > 0) {
        return this.best_selling_product;
      } else {
        return [];
      }
    },
  },
  methods: {
    navigator() {
      if (this.$route.name == "shop") {
        window.scroll(0, 500);
        this.$store.commit("setActiveTab", "product");
      } else {
        this.$router.push({ name: "product.by.selling" });
      }
    },
    checkHomeComponent(component_name) {
      let component = this.homeResponse.find((data) => data == component_name);

      if (component) {
        return (this.show_shimmer = false);
      }
    },
    checkShopComponent(component_name) {
      if (this.shopResponse) {

        let component = this.shopResponse.find(
          (data) => data == component_name
        );

        if (component) {
          return (this.show_shimmer = false);
        }
      }
      return (this.show_shimmer = false);
    },
  },
};
</script>

<style scoped>
.btn-style {
  background-color: #161616c2 !important;
  border-radius: 30px;
}

.video-container {
  position: relative;
  width: 100%;
  padding-top: 56.25%; /* 16:9 aspect ratio */
}

.video-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 70%;
  object-fit: cover;
}

.shade {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 70%;
  background-color: rgba(0, 0, 0, 0.5); /* Adjust the opacity as needed */
}

.button-container {
  position: absolute;
  top: 35%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.button-container .btn {
  font-size: 18px;
  padding: 10px 20px;
}

.offer-section {
  margin: 0% 5% 0% 0%;
}

.get-back-section {
  background-color: #0c0d10;
  padding: 5%;
}
.custom-padding {
  padding: 5% 0;
}

.custom-padding-bottom {
  padding-bottom: 5%;
}

.custom-padding-top.bg-color {
  padding-bottom: 2%;
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
.bg-color-products {
  background-color: #f2f2f2;
}

/* CSS */
.button-17 {
  align-items: center;
  appearance: none;
  background-color: #fff;
  border-radius: 24px;
  border-style: none;
  box-shadow: rgba(0, 0, 0, 0.2) 0 3px 5px -1px,
    rgba(0, 0, 0, 0.14) 0 6px 10px 0, rgba(0, 0, 0, 0.12) 0 1px 18px 0;
  box-sizing: border-box;
  color: #3c4043;
  cursor: pointer;
  display: inline-flex;
  fill: currentcolor;
  font-family: "Google Sans", Roboto, Arial, sans-serif;
  font-size: 14px;
  font-weight: 500;
  height: 48px;
  justify-content: center;
  letter-spacing: 0.25px;
  line-height: normal;
  max-width: 100%;
  overflow: visible;
  padding: 2px 24px;
  position: relative;
  text-align: center;
  text-transform: none;
  transition: box-shadow 280ms cubic-bezier(0.4, 0, 0.2, 1),
    opacity 15ms linear 30ms, transform 270ms cubic-bezier(0, 0, 0.2, 1) 0ms;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  width: auto;
  will-change: transform, opacity;
  z-index: 0;
}

.button-17:hover {
  background: #f6f9fe;
  color: #174ea6;
}

.button-17:active {
  box-shadow: 0 4px 4px 0 rgb(60 64 67 / 30%),
    0 8px 12px 6px rgb(60 64 67 / 15%);
  outline: none;
}

.button-17:focus {
  outline: none;
  border: 2px solid #4285f4;
}

.button-17:not(:disabled) {
  box-shadow: rgba(60, 64, 67, 0.3) 0 1px 3px 0,
    rgba(60, 64, 67, 0.15) 0 4px 8px 3px;
}

.button-17:not(:disabled):hover {
  box-shadow: rgba(60, 64, 67, 0.3) 0 2px 3px 0,
    rgba(60, 64, 67, 0.15) 0 6px 10px 4px;
}

.button-17:not(:disabled):focus {
  box-shadow: rgba(60, 64, 67, 0.3) 0 1px 3px 0,
    rgba(60, 64, 67, 0.15) 0 4px 8px 3px;
}

.button-17:not(:disabled):active {
  box-shadow: rgba(60, 64, 67, 0.3) 0 4px 4px 0,
    rgba(60, 64, 67, 0.15) 0 8px 12px 6px;
}

.button-17:disabled {
  box-shadow: rgba(60, 64, 67, 0.3) 0 1px 3px 0,
    rgba(60, 64, 67, 0.15) 0 4px 8px 3px;
}

.text-style {
  color: #fff;
  font-family: monospace;
  font-size: 70px;
}

span {
  display: inline-block;
}

.text-style-second {
  display: block;
  font-family: monospace;
  font-weight: bold;
  font-size: 80px;
  color: #fff;
}

.text-style-third {
  display: block;
  font-family: monospace;
  font-size: 30px;
  color: #fff;
}
.button-extra-style {
  width: 330px;
  height: 40px;
}
.input-extra-style {
  border: 1px solid;
  border-color: #fff;
  background-color: #000;
  border-radius: 30px;
  text-align: center;
  height: 40px;
  padding: 0px 25px 0px 25px;
  display: block;
  margin: 1% 0% 1% 10%;
}
.align-center {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  /* height: 100%; */
  flex-direction: column;
  /* height: 100%; */
}
.button-extra-style {
  margin: 1% 0% 1% 10%;
}
</style>
