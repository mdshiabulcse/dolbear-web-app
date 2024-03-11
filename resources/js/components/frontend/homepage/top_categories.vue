<template>
 
</template>

<script>
import shimmer from "../partials/shimmer";
import VueSlickCarousel from 'vue-slick-carousel'

export default {
  name: "top_categories",
  props: ['top_categories'],
  components: {
    shimmer, VueSlickCarousel
  },
  data() {
    return {
      slick_settings: {
        "dots": false,
        "edgeFriction": 0.35,
        "infinite": true,
        "arrows": true,
        "autoplay": false,
        "slidesToShow": 6,
        "slidesToScroll": 4,
        "centerPadding": '50px',
        "responsive": [
          {
            "breakpoint": 1024,
            "settings": {
              "slidesToShow": 5,
              "slidesToScroll": 2,
            }
          },
          {
            "breakpoint": 768,
            "settings": {
              "slidesToShow": 3,
              "slidesToScroll": 2,
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
      show_shimmer: true,
      current_slider : 0
    }
  },

  mounted() {
    this.checkHomeComponent("top_category");
  },
  watch: {
    homeResponse() {
      this.checkHomeComponent("top_category");
    }
  },
  computed: {
    countCategories() {
      if (this.top_categories && this.top_categories.length > 0) {
        return this.top_categories;
      } else {
        return [];
      }
    },
  },
  methods: {
    checkHomeComponent(component_name) {
      let component = this.homeResponse.find(data => data == component_name);

      if (component) {
        return this.show_shimmer = false;
      }
    },
  }
}
</script>
