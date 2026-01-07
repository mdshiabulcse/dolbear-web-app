<template>
    <div class="container mb-5">
        <h2 class="mt-3 mb-3">Featured Products</h2>
        <div class="d-flex flex-wrap gap-2 featured_product_items">
      
            <div v-for="product in products" :key="product.id">
                <product_card :product="product"></product_card>
            </div>
        </div>
    </div>
</template>
<script>
import product_card from '../../common/product_card.vue';

export default {
    components: {
        product_card
    },

    data() {
        return {
            products: [],
        }
    },

    async created() {
        await this.loadNewProducts();
    },

    methods: {
        async loadNewProducts() {

            const baseUrl = `${window.location.protocol}//${window.location.host}`;

            const url = `${baseUrl}/home/featured-products`;

            const response = await axios.get(url);
            this.products = response?.data?.products;

        }
    }
    
}
</script>
<style lang="">
   
   
</style>