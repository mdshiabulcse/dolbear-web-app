<template>
    <div>
      <!-- best categories -->
      <section class="best-categories">
        <div class="container overflow-hidden">
          <h2 class="section-heading text-center text-light mb-md-5 mb-3">Best Categories</h2>
          <div class="categories-list d-flex align-items-center justify-content-md-between">
            <OwlCarousel
              ref="carousel"
              :autoplay="true"
              :nav="false"
              :loop="true"
              :responsive="responsiveConfig"
              :dots="false"
              :autoplayTimeout="3000"
              :autoplayHoverPause="true"
              :key="carouselKey"
            >
              <div v-for="(category, index) in popularCategoriesData" :key="index">
                <a
                  :href="getUrl('category/' + category.slug)"
                  @click.prevent="routerNavigator('product.by.category', category.slug)"
                  class="items d-flex flex-column align-items-center"
                >
                  <div class="item-images">
                    <img :src="category.popular_image" :alt="category.title" />
                  </div>
                  <p class="mb-0 mt-3 text-center">{{ category.title }}</p>
                </a>
              </div>
            </OwlCarousel>
          </div>
        </div>
      </section>
    </div>
  </template>
  
  <script>
  import shimmer from "../partials/shimmer";
  import OwlCarousel from "vue-owl-carousel";
  
  export default {
    name: "popular_categories",
    components: { shimmer, OwlCarousel },
    props: ["popular_categories"],
    data() {
      return {
        carouselKey: 0, // Key to force reinitialization of Owl Carousel
        popularCategoriesData: [],
        show_shimmer: true,
        responsiveConfig: {
          0: { items: 1 },      // Mobile phones (up to 600px)
          576: { items: 2 }, 
          768: { items: 4 },    // Tablets (up to 768px)
          992: { items: 4 },    // Medium screens (up to 992px)
          1200: { items: 5 },   // Large screens (up to 1200px)
          1400: { items: 6 }    // Extra-large screens (1400px and above)
        }
      };
    },
    mounted() {
      this.popularCategories();
      window.addEventListener("resize", this.handleResize);
    },
    beforeDestroy() {
      window.removeEventListener("resize", this.handleResize);
    },
    methods: {
      popularCategories() {
        let url = this.getUrl("categories/popular");
        this.$Progress.start();
        axios
          .get(url, { params: this.$route.params.type })
          .then((response) => {
            if (response.data.error) {
              this.$Progress.fail();
              toastr.error(response.data.error, this.lang.Error + " !!");
            } else {
              this.popularCategoriesData = response.data.data;
              this.$Progress.finish();
              this.carouselKey += 1; // Trigger re-render of carousel
            }
          })
          .catch((error) => {
            this.$Progress.fail();
          });
      },
      handleResize() {
        // Reinitialize Owl Carousel on window resize
        this.carouselKey += 1; // Increment carousel key to force re-render
      },
      routerNavigator(route, slug) {
        this.$router.push({ name: route, params: { slug } });
      }
    }
  };
  </script>
  
  <style scoped>
  .border-bottom {
    border-bottom: none !important;
  }
  .text-align-center {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-weight: 600;
  }
  .bg-color {
    background-color: #0B0B0B;
    padding-top: 5%;
    padding-bottom: 10%;
  }
  .owl-nav .owl-prev,
  .owl-nav .owl-next {
    color: #000;
  }
  </style>
  