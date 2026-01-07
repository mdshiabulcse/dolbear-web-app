<template>
  
</template>

<script>
import shimmer from "../partials/shimmer";
import VueSlickCarousel from 'vue-slick-carousel'

export default {
  name: "brand",
  components: {shimmer, VueSlickCarousel},
  props: ['brands'],
  data: () => ({
    slick_settings: {
      "dots": false,
      "infinite": true,
      "arrows": true,
      "autoplay": false,
      "slidesToShow": 6,
      "slidesToScroll": 6,
      "responsive": [
        {
          "breakpoint": 1024,
          "settings": {
            "slidesToShow": 5,
            "slidesToScroll": 5,
          }
        },
        {
          "breakpoint": 768,
          "settings": {
            "slidesToShow": 3,
            "slidesToScroll": 3,
          }
        },
        {

          "breakpoint": 480,
          "settings": {
            "slidesToShow": 2,
            "slidesToScroll": 2
          }
        },
        {

          "breakpoint": 320,
          "settings": {
            "slidesToShow": 2,
            "slidesToScroll": 2
          }
        }
      ]
    },
    show_shimmer: true
  }),
  mounted() {
    this.checkHomeComponent("popular_brands");
  },
  watch: {
    homeResponse() {
      this.checkHomeComponent("popular_brands");
    }
  },
  computed: {
    countBrand() {
      return this.lengthCounter(this.brands);
    }
  },
  methods: {
    checkHomeComponent(component_name) {
      let component = this.homeResponse.find(data => data == component_name);

      if (component) {
        return this.show_shimmer = false;
      }
    }
  }
}
</script>
