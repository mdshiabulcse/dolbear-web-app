<template>
  <div class="category-section mb-5">
    <div class="container">
      <div class="category-grid">
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
  margin-bottom: 20px;
}

.category-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.category-item {
  flex: 0 0 calc(16.666% - 10px); /* 6 per row: 100%/6 - gap */
  max-width: calc(16.666% - 10px);
}

/* Extra Large screens - 6 per row */
@media (min-width: 1400px) {
  .category-item {
    flex: 0 0 calc(16.666% - 10px);
    max-width: calc(16.666% - 10px);
  }
}

/* Large screens - 5 per row */
@media (min-width: 1200px) and (max-width: 1399px) {
  .category-item {
    flex: 0 0 calc(20% - 9.6px); /* 5 per row: 100%/5 - (12px * 4 gaps / 5 items) */
    max-width: calc(20% - 9.6px);
  }
}

/* Medium screens - 4 per row */
@media (min-width: 992px) and (max-width: 1199px) {
  .category-item {
    flex: 0 0 calc(25% - 9px); /* 4 per row: 100%/4 - (12px * 3 gaps / 4 items) */
    max-width: calc(25% - 9px);
  }
}

/* Tablet - 4 per row */
@media (min-width: 768px) and (max-width: 991px) {
  .category-item {
    flex: 0 0 calc(25% - 9px);
    max-width: calc(25% - 9px);
  }
}

/* Small mobile - 4 per row */
@media (max-width: 767px) {
  .category-item {
    flex: 0 0 calc(25% - 4.5px); /* 4 per row: 100%/4 - (6px * 3 gaps / 4 items) */
    max-width: calc(25% - 4.5px);
  }

  .category-grid {
    gap: 6px;
  }
}
</style>



