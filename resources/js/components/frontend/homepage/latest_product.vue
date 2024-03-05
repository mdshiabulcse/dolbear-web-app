<template>
	
</template>
<script>
import productCarousel from "../pages/product-carousel";
import shimmer from "../partials/shimmer";

export default {
	name: "latest_products",
	data() {
		return {
			show_shimmer: true,
		};
	},
	components: {
		productCarousel,
		shimmer,
	},
	props: ["latest_products"],
	mounted() {
		this.checkHomeComponent("latest_product");
	},
	watch: {
		homeResponse() {
			this.checkHomeComponent("latest_product");
		},
	},
	computed: {
		products() {
			if (this.latest_products && this.latest_products.length > 0) {
				return this.latest_products;
			} else {
				return [];
			}
		},
	},
	methods: {
		navigator() {
			this.$router.push({ name: "product" });
		},
		checkHomeComponent(component_name) {
			let component = this.homeResponse.find((data) => data == component_name);

			if (component) {
				return (this.show_shimmer = false);
			}
		},
	},
};
</script>
