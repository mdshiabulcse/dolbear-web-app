<template>
  <div class="container" style="min-height: auto; ">
    <div class="d-flex flex-wrap justify-content-center gap-2 ">
      <div v-for="category in categories" :key="category.id">
        <router-link :to="{ name: 'product.by.category', params: { slug: category.slug } }">
          <category_card :category="category"></category_card>
        </router-link>

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



