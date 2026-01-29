<template>
    <div class="container mb-5">
        <h2 class="mt-3 mb-3">Featured Products</h2>
        <div class="featured_product_items">
            <div v-for="product in products" :key="product.id" class="product-item">
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
<style scoped>
.featured_product_items {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.product-item {
    flex: 0 0 calc(16.666% - 10px);
    max-width: calc(16.666% - 10px);
}

/* Extra Large screens - 6 per row */
@media (min-width: 1400px) {
    .product-item {
        flex: 0 0 calc(16.666% - 10px);
        max-width: calc(16.666% - 10px);
    }
}

/* Large screens - 5 per row */
@media (min-width: 1200px) and (max-width: 1399px) {
    .product-item {
        flex: 0 0 calc(20% - 9.6px);
        max-width: calc(20% - 9.6px);
    }
}

/* Medium screens - 4 per row */
@media (min-width: 992px) and (max-width: 1199px) {
    .product-item {
        flex: 0 0 calc(25% - 9px);
        max-width: calc(25% - 9px);
    }
}

/* Tablet - 3 per row */
@media (min-width: 768px) and (max-width: 991px) {
    .product-item {
        flex: 0 0 calc(33.333% - 8px);
        max-width: calc(33.333% - 8px);
    }
}

/* Small mobile - 2 per row */
@media (max-width: 767px) {
    .product-item {
        flex: 0 0 calc(50% - 6px);
        max-width: calc(50% - 6px);
    }

    .featured_product_items {
        gap: 12px;
    }
}
</style>