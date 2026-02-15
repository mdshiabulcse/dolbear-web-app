<template>
  <div class="category-section mb-5">
    <!-- Page Header - Black & White Brand Colors -->
    <div class="page-header-banner">
      <div class="container">
        <h1 class="page-title">All Categories</h1>
        <p class="page-subtitle">Browse through our complete collection of categories</p>
      </div>
    </div>

    <!-- Categories Grid - Same as existing categories.vue -->
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
import category_card from '../../common/category_card.vue';

export default {
  name: 'allCategories',
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
      const url = `${baseUrl}/home/all-categories`;

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
/* Page Header - Black & White Brand Colors */
.page-header-banner {
  background: #000000;
  padding: 40px 0;
  margin-bottom: 30px;
  text-align: center;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.page-subtitle {
  font-size: 14px;
  color: #cccccc;
  margin-bottom: 0;
}

/* Category Section - Same as existing categories.vue */
.category-section {
  margin-bottom: 20px;
}

.category-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.category-item {
  flex: 0 0 calc(16.666% - 10px);
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
    flex: 0 0 calc(20% - 9.6px);
    max-width: calc(20% - 9.6px);
  }
}

/* Medium screens - 4 per row */
@media (min-width: 992px) and (max-width: 1199px) {
  .category-item {
    flex: 0 0 calc(25% - 9px);
    max-width: calc(25% - 9px);
  }
}

/* Tablet - 4 per row */
@media (min-width: 768px) and (max-width: 991px) {
  .category-item {
    flex: 0 0 calc(25% - 9px);
    max-width: calc(25% - 9px);
  }

  .page-header-banner {
    padding: 30px 0;
  }

  .page-title {
    font-size: 24px;
  }

  .page-subtitle {
    font-size: 13px;
  }
}

/* Small mobile - 4 per row */
@media (max-width: 767px) {
  .category-item {
    flex: 0 0 calc(25% - 4.5px);
    max-width: calc(25% - 4.5px);
  }

  .category-grid {
    gap: 6px;
  }

  .page-header-banner {
    padding: 25px 0;
    margin-bottom: 20px;
  }

  .page-title {
    font-size: 20px;
  }

  .page-subtitle {
    font-size: 12px;
  }
}

/* Extra small mobile */
@media (max-width: 480px) {
  .page-header-banner {
    padding: 20px 0;
  }

  .page-title {
    font-size: 18px;
  }

  .page-subtitle {
    font-size: 11px;
  }
}
</style>