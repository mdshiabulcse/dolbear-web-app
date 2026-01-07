<template>
  <div>
    <div>
  <!-- must have dolbear -->
<section class="must-dolbear">
<div class="container">
  <h2 class="section-heading text-center mb-3 mb-md-5">Must Have Dolbear Selections</h2>
  <div class="categori-button-list d-flex align-items-center justify-content-center">
    <a @click="handleTabChange('is_new_arrived')" class="btn-item cursor-pointer" :class="{ 'active': currentTab === 'is_new_arrived' }">New Arrived</a>
    <a @click="handleTabChange('is_best_seller')" class="btn-item cursor-pointer" :class="{ 'active': currentTab === 'is_best_seller' }">Best Seller</a>
    <a @click="handleTabChange('is_bundle_deals')" class="btn-item cursor-pointer" :class="{ 'active': currentTab === 'is_bundle_deals' }">Bundle Deals</a>
  </div>

  <div v-if="sortedProduct.length > 0">

      <carousel
        :autoplay="true"
        :nav = "false"
        :margin = "50"
        :items="sliderProductsShow"
        :dots = "false"
        :autoplayTimeout = "3000"
        :autoplayHoverPause = "true"
        :key="carouselKey"
        >

            <div class="product-slider" v-for="(item, index) in sortedProduct" :key="index">
              <a :href="'product/' + item.slug" class="img-fluid text-center">
              <div class="overflow-hidden"><img :src="item.image_190x230" alt="Image 1" class="img-fluid text-center"></div>
                <p class="product-name mb-4 mt-3">{{ item.product_name }}</p>
                <p class="mb-0 product-price">BDT {{ item.price }}</p>
              </a>
            </div>

      </carousel>

  </div>

</div>
</section>
<!-- categori -->
<section class="categori">
<div class="container">
  <div class="categories-list d-flex align-items-center justify-content-between">
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/card.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Phone</p>
    </a>
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/eearphone.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Head Phone</p>
    </a>
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/cat1.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Phone</p>
    </a>
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/cat1.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Phone</p>
    </a>
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/cat1.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Phone</p>
    </a>
    <a href="#" class="items d-flex flex-column align-items-center">
      <div class="item-images">
        <img src="../../../../../public/images/img/cat1.png" alt="categores">
      </div>
      <p class="mb-0 mt-3 text-center">Phone</p>
    </a>
  </div>

</div>
</section>
<!-- offer -->
<section class="offer pb-3 pb-md-5 bg-light-white">
<div class="container">
  <div class="row">
    <div class="col-6">
      <a href="#" class="d-block">
        <img src="../../../../../public/images/img/offer1.png" alt="offer" class="img-fluid">
      </a>
    </div>
    <div class="col-6">
      <a href="#" class="d-block">
        <img src="../../../../../public/images/img/offer2.png" alt="offer" class="img-fluid">
      </a>
    </div>
  </div>
</div>
</section>
<!-- Featured Product -->
<section class="featured bg-light-white">
<div class="container">
  <h2 class="section-heading text-center mb-3 mb-md-5">Featured Product</h2>
  <div class="featured-grid">
    <template>
      <a v-for="(item, index) in featuredProduct" :key="index" :href="'product/' + item.slug" class="d-block featured-item" >
        <div class='overflow-hidden'><img :src="item.image_190x230" alt="featured images" class="img-fluid text-center"></div>
        <p class="product-name mb-4 mt-3">{{ item.product_name }}</p>
        <p class="mb-0 product-price">BDT {{ item.price }}</p>
      </a>
    </template>


  </div>
</div>
</section>
<!-- video sectio -->
<section class="video-sec">
<template>
<video class="d-none d-sm-block" autoplay preload playsinline loop :poster="posterLarge" muted>
<source :src="videoWebm" type="video/webm">
<source :src="videoMp4" type="video/mp4">
<source :src="videoOgg" type="video/ogg">
</video>
<video class="d-sm-none" autoplay preload playsinline loop :poster="posterSmall" muted>
<source :src="videoWebm" type="video/webm">
<source :src="videoMp4" type="video/mp4">
<source :src="videoOgg" type="video/ogg">
</video>
</template>
<!-- button -->
<button class="video-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">New Arrivals
  <svg class="arrow ms-3" width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1.82822e-07 0.499999L9 7L-9.53674e-07 13.5L1.82822e-07 0.499999Z" fill="white"/>
  </svg>
  </button>
  <!-- youtube video -->
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" aria-hidden="true" ><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        <div class="modal-body">
          <iframe width="100%" height="100%" src="https://www.youtube.com/embed/mFv0tMZHMfA?enablejsapi=1&origin=https%3A%2F%2Fwww.anker.com&widgetid=6" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- recomonded slider -->
<section class="recomonded">
<div class="container">
  <h2 class="section-heading text-center mb-3 mb-md-5">Recommended By</h2>
  <div class="recomonded-accordion pt-3 pt-md-5">
    
    <div class="recomonded-accordion-item" v-for="(item, index) in recommendation" :key="index">
      <div class="item-imgs">
        <img :src="item.image" alt="images" class="img-fluid">
      </div>
      <div class="item-text">
        <p>{{ item.name }}</p>
        <p>{{ item.description }}</p>
      </div>
    </div>
    
  </div>

</div>
</section>
<!-- get back 15% off -->
<section class="first-offer bg-black">
<div class="container">
  <div class="row justify-content-center align-items-center">
    <div class="col-lg-4 mb-3 mb-lg-0">
      <h1>
        <span class="d-block text-1">Get Back</span>
        15% OFF
        <span class="text-2">On Your First Order</span>
      </h1>
    </div>
    <div class="col-lg-1"></div>
    <div class="col-lg-4">
      <div class="news-latter-group">
        <input type="email" class="form-control w-100 mb-3" id="InputEmail" aria-describedby="emailHelp" placeholder="your email">
        <button class="w-100 send-btn">Send</button>
      </div>
    </div>
  </div>
</div>
</section>

<!-- footer -->

</div>
  </div>
</template>
<script>
import productCarousel from "../pages/product-carousel";
import companyPolicySection from "./company_policy_section.vue";
import shimmer from "../partials/shimmer";
import carousel from 'vue-owl-carousel';

export default {
  name: "best_selling_product",
  components: {
    productCarousel,
    shimmer,
    carousel,
    companyPolicySection,
  },
  props: ["best_selling_product", "latest_products"],
  data() {
    return {
      show_shimmer: true,
      posterLarge: 'https://cdn.shopify.com/s/files/1/0650/3769/7280/files/charge-brand-video.jpg?v=1671519405',
      posterSmall: 'https://cdn.shopify.com/s/files/1/0650/3769/7280/files/charge_brand_video_0-767.jpg?v=1671519405',
      videoWebm: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.webm?_a=ATRSRAA0',
      videoMp4: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.mp4?_a=ATRSRAA0',
      videoOgg: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.ogv?_a=ATRSRAA0',

      latestProduct: [],
      tabList: ['is_new_arrived', 'is_best_seller', 'is_bundle_deals'],
      currentTab: "is_new_arrived",
      featuredProduct: [],
      availableProduct: [],
      sortedProduct: [],
      recommendation: [],
      screenWidth: window.innerWidth,
      sliderProductsShow: '',
      carouselKey: ''
    };
  },
  mounted() {
    this.checkHomeComponent("best_selling_product");
    this.checkShopComponent("best_selling_product");
    this.updateWidth();
    this.availableProducts();
    this.recommendationData();
  },
  created() {
    window.addEventListener('resize', this.updateWidth);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.updateWidth);
  },
  watch: {
    best_selling_product: {
      immediate: true,
      handler(newValue) {
        if (Array.isArray(newValue)) {
          // this.featuredProduct = newValue.filter(product => product.is_catalog);
          this.featuredProduct = newValue;
        }
      },
    },
    sortedProduct() {
      this.carouselKey++
    },

    latest_product: {
      immediate: true,
      handler(newValue) {
        if (Array.isArray(newValue)) {
          // this.featuredProduct = newValue.filter(product => product.is_catalog);
          this.latestProduct = newValue;
        }
      },
    },

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
    all_Products() {

      if (this.latest_products
          && this.latest_products.length > 0) {
        return this.latest_products;

      } else {
        return [];
      }
    },
  },
  methods: {
    handleTabChange(value){
      this.sortedProduct = []
      this.currentTab = value
      this.sortedProduct = this.availableProduct.filter(product => product[value]);
    },
    availableProducts() {
      let url = this.getUrl('products/available');
      this.$Progress.start();
      axios.get(url,{params:this.$route.params.type}).then((response) => {
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          this.availableProduct = response.data.data
          this.sortedProduct = this.availableProduct.filter(product => product[this.currentTab]);

          this.$Progress.finish();
        }
      }).catch((error) => {
        this.$Progress.fail();
      })
    },
    recommendationData() {
      let url = this.getUrl('recommended/all');
      this.$Progress.start();
      axios.get(url,{params:this.$route.params.type}).then((response) => {
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          this.recommendation = response.data.data

          setTimeout(() => {
            this.initAccordion()
          }, 2000);


          this.$Progress.finish();
        }
      }).catch((error) => {
        this.$Progress.fail();
      })
    },
    // Recomonded By accordion hover effect function
    initAccordion() {
      const accordionItems = document.querySelectorAll('.recomonded-accordion-item');

      // Loop through each accordion item
      accordionItems.forEach(item => {
        // Add event listeners for hover
        item.addEventListener('mouseover', () => {
          // Remove 'active' class from all item-text elements
          document.querySelectorAll('.item-text').forEach(text => {
            text.classList.remove('active');
          });
          // Add 'active' class to item-text in current accordion item
          item.querySelector('.item-text').classList.add('active');
        });

        // Add mouseout event to keep 'active' class when mouse leaves the item
        item.addEventListener('mouseout', () => {
          item.querySelector('.item-text').classList.add('active');
        });
      });
    },
    //product carousel responsive
    updateWidth() {
      this.screenWidth = window.innerWidth;

      if (this.screenWidth < 575){
          this.sliderProductsShow = 2;
      }else if(this.screenWidth >= 575 && this.screenWidth < 800){
          this.sliderProductsShow = 3;
      }else {
          this.sliderProductsShow = 4;
      }
    },

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
.product-slider {
  padding: 20px;
    background-color: white;
    border-radius: 10px;
}

.cursor-pointer {
    cursor: pointer;
  }

 
</style>
