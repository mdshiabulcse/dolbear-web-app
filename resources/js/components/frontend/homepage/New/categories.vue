<template>
  <div class="category-section">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-center category-grid">
        <div v-for="category in categories" :key="category.id" class="category-item">
          <router-link :to="{ name: 'product.by.category', params: { slug: category.slug } }">
            <category_card :category="category"></category_card>
          </router-link>
        </div>
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

<style scoped>
.category-section {
  margin-bottom: 40px;
}

.category-grid {
  gap: 12px;
}

.category-item {
  flex: 0 0 auto;
}

@media (min-width: 1400px) {
  .category-section {
    margin-bottom: 50px;
  }

  .category-grid {
    gap: 15px;
  }
}

@media (max-width: 992px) {
  .category-section {
    margin-bottom: 30px;
  }

  .category-grid {
    gap: 10px;
  }
}

@media (max-width: 576px) {
  .category-section {
    margin-bottom: 20px;
  }

  .category-grid {
    gap: 8px;
  }
}
</style>



