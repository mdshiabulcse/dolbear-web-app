<template>
    <div class="product-grid-container" v-if="products.length > 0">
      <product_card
       v-if="products.length > 0"
        v-for="(product, index) in products"
        :key="index"
        :product="product"
        :maxWidth="gridView == 'grid-view-tab' ? '268px' : '100%'"
      />
      <p v-else>No product found</p>

    </div>
  </template>

  <script>
  import product_card from "../common/product_card.vue";

  export default {
    components: {
      product_card,
    },
    props: {
      products: {
        type: Array,
        required: true,
      },
      gridView: {
        type: String,
        default: 'grid-view-tab',
      }
    },
   mounted() {
   }
  };
  </script>

<style scoped>
.product-grid-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  width: 100%;
}

/* Small mobile - still 2 columns */
@media screen and (max-width: 360px) {
  .product-grid-container {
    gap: 0.25rem;
  }
}

/* Tablet - 3 columns */
@media screen and (min-width: 768px) and (max-width: 1024px) {
  .product-grid-container {
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
  }
}

/* Desktop - 4 columns */
@media screen and (min-width: 1025px) {
  .product-grid-container {
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
  }
}

.list-view p.product-card-title {
    width: 100%;
}

/* Ensure list view still works */
.product-grid-container.list-view {
  display: flex;
  flex-direction: column;
}

</style>
  