<template>
  <div class="container">
    <div class="d-flex flex-wrap gap-2 mt-2">
      <div v-for="category in categories" :key="category.id">
          <category_card :category="category"></category_card>
     
      </div>
    </div>


  </div>
</template>

<script>
import { ProgressPlugin } from 'webpack';
import category_card from '../../common/category_card.vue';
export default {
  name: 'categories',
  components: {
    category_card
  },

  data() {
    return {
      categories: [],
    }
  },
  async created() {
    await this.loadCategories();
  },

  methods: {
    async loadCategories() {

      const baseUrl = `${window.location.protocol}//${window.location.host}`;

      const url = `${baseUrl}/home/categories`;

      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.categories = response.data.categories;
        }
      });
    }
  }


};
</script>

