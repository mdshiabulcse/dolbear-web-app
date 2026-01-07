<template>
  <div>
       
   <!-- best categories -->
   <section class="best-categories">
      <div class="container overflow-hidden">
        <h2 class="section-heading text-center text-light mb-md-5 mb-3">Best Categories</h2>
        <div class="categories-list d-flex  align-items-center justify-content-md-between">
         <OwlCarousel  :autoplay="true"
        :nav = "false"
        :loop = "true"
        :responsive="responsiveConfig"
        :dots = "false"
        :autoplayTimeout = "3000"
        :autoplayHoverPause = "true"
        :key="carouselKey">
          <div v-for="(category,index) in popularCategoriesData" :key="index">
            <a :href="getUrl('category/'+category.slug)" @click.prevent="routerNavigator('product.by.category',category.slug)" class="items d-flex flex-column align-items-center">
              <div class="item-images">
                <img :src="category.popular_image"
                   :alt="category.title">
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
import OwlCarousel from 'vue-owl-carousel'


export default {
  name: "popular_categories",
  components: {shimmer,OwlCarousel},
  props: ['popular_categories'],
  data() {
    return {
      carouselKey: 0,
      popularCategoriesData: [],
   
      show_shimmer: true,
      responsiveConfig: {
        0: { items: 2 },
        600: { items: 4 },
        1000: { items: 4 },
        1200: { items: 5 }
      }
    }
  },

  mounted() {
    // this.checkHomeComponent("popular_category");
    this.popularCategories();
    window.addEventListener('resize', this.handleResize);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  },
  watch: {
    homeResponse() {
      this.checkHomeComponent("popular_category");
    }
  },
  computed: {
    countCategories() {
      if (this.popular_categories && this.popular_categories.length > 0) {
        return this.popular_categories;
      } else {
        return [];
      }
    },
  },
  methods: {
    popularCategories() {
      let url = this.getUrl('categories/popular');
      this.$Progress.start();
      axios.get(url,{params:this.$route.params.type}).then((response) => {
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.popularCategoriesData = response.data.data
          this.$Progress.finish();
          this.carouselKey += 1
         
        }
      }).catch((error) => {
        this.$Progress.fail();
      })
    },
    checkHomeComponent(component_name) {
      let component = this.homeResponse.find(data => data == component_name);

      if (component) {
        return this.show_shimmer = false;
      }
    },
    handleResize() {
      // Destroy the carousel and reinitialize it
      const owl = this.$refs.owl.$el;
      $(owl).trigger('destroy.owl.carousel'); // Destroy carousel
      $(owl).owlCarousel(this.responsiveConfig); // Reinitialize with responsive config
    }
  }
}
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
.owl-nav .owl-prev, .owl-nav .owl-next {
  color: #000;
}
</style>
