<template>
	<section class="products-section bg-white item-space-rmv" v-if="lengthCounter(products) > 0">
		<div class="container">
			<div class="title justify-content-between" :class="{ 'title-bg title-btm-space' : addons.includes('ishopet') }">
				<h1> {{ lang.gift_idea }}</h1>
				<a :href="getUrl('gift-idea')" @click.prevent="routerNavigator('gift.idea')">{{ lang.more_products }}<span class="icon mdi mdi-name mdi-arrow-right"></span></a>
			</div>
			<productCarousel :products="products" :number="12" :grid_class="'grid-6'"></productCarousel>
    </div>
  </section>
	<section class="products-section bg-white" v-else-if="show_shimmer">
		<div class="container">
			<ul class="products grid-6">
				<li v-for="(product, index) in 6" :key="index">
					<div class="sg-product">
						<a href="javascript:void(0)">
							<shimmer :height="364"></shimmer>
						</a> </div
					><!-- /.sg-product -->
				</li>
			</ul> </div
		><!-- /.container --> </section
	><!-- /.section -->
</template>
<script>
import productCarousel from "../pages/product-carousel";
import shimmer from "../partials/shimmer";

export default {
	name: "gift_idea",
	data() {
		return {
			show_shimmer: true,
		};
	},
	components: {
		productCarousel,
		shimmer,
	},
	props: ["gift_idea"],
	mounted() {
		this.checkHomeComponent("gift_idea");
	},
	watch: {
		homeResponse() {
			this.checkHomeComponent("gift_idea");
		},
	},
	computed: {
		products() {
			if (this.gift_idea && this.gift_idea.length > 0) {
				return this.gift_idea;
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
