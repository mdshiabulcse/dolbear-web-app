<template>
    <div  :class="gridView == 'grid-view-tab' ?  'd-flex flex-wrap gap-2':'d-flex flex-column gap-2 list-view'" v-if="products.length > 0">
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

<style>
.list-view p.product-card-title {
    width: 100%;
}



</style>
  