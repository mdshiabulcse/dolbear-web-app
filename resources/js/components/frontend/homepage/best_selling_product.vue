<template>
	<section class="products-section item-space-rmv bg-color-products" v-if="lengthCounter(products) > 0">
		<div class="container" v-if="products[0] != 'id'">
			<!-- <div class="title justify-content-between" :class="{ 'title-bg title-btm-space' : addons.includes('ishopet') }">
				<h1> {{ lang.best_selling_product }}ijadkasf</h1>
				<a :href="getUrl('best-selling/products')" @click.prevent="navigator">{{ lang.more_products }} <span class="icon mdi mdi-name mdi-arrow-right"></span></a>
			</div> -->

			<div class="custom-padding "></div>

      <h1 class="text-center custom-padding-bottom ">Products</h1>

	  <div class="container ">
        <div class="row justify-content-center">
          <div class="col-auto">
            <button type="button" class="btn custom-btn">New Arrivals</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Best Sellers</button>
          </div>
          <div class="col-auto">
            <button type="button" class="btn custom-btn">Bundle Deals</button>
          </div>
        </div>
      </div>

	  <div class="custom-padding-bottom "></div>


			<productCarousel :products="products" :number="12" :grid_class="'grid-4'"></productCarousel>
    </div><!-- /.container -->
	<companyPolicySection></companyPolicySection>

  </section><!-- /.section -->
	<!-- <section class="products-section bg-white selling" v-else-if="show_shimmer">
		<div class="container">
			<ul class="products grid-4">
				<li v-for="(product, index) in 6" :key="index">
					<div class="sg-product">
						<a href="javascript:void(0)">
							<shimmer :height="364"></shimmer>
						</a> </div>
				</li>
			</ul>
		</div>
	</section> -->


</template>
<script>
import productCarousel from "../pages/product-carousel";
import companyPolicySection from "./company_policy_section.vue";
import shimmer from "../partials/shimmer";

export default {
	name: "best_selling_product",
	components: {
		productCarousel,
		shimmer,
		companyPolicySection,
	},
	props: ["best_selling_product"],
	data() {
		return {
			show_shimmer: true,
		};
	},
	mounted() {
		this.checkHomeComponent("best_selling_product");
		this.checkShopComponent("best_selling_product");
	},
	watch: {
		homeResponse() {
			this.checkHomeComponent("best_selling_product");
		},
    shopResponse() {
			this.checkShopComponent("best_selling_product");
		},
	},
	computed: {
		products() {
			if (this.best_selling_product && this.best_selling_product.length > 0) {
				return this.best_selling_product;
			} else {
				return [];
			}
		},
	},
	methods: {
		navigator() {
			if (this.$route.name == "shop") {
				window.scroll(0, 500);
				this.$store.commit("setActiveTab", "product");
			} else {
				this.$router.push({ name: "product.by.selling" });
			}
		},
		checkHomeComponent(component_name) {
			let component = this.homeResponse.find((data) => data == component_name);

			if (component) {
				return (this.show_shimmer = false);
			}
		},
    checkShopComponent(component_name) {
      if (this.shopResponse)
      {
        let component = this.shopResponse.find((data) => data == component_name);

        if (component) {
          return (this.show_shimmer = false);
        }
      }
      return (this.show_shimmer = false);
    },
	},
};
</script>

<style scoped>
.custom-padding {
  padding: 5% 0;
}

.custom-padding-bottom {
  padding-bottom: 5%;
}

.custom-btn {
		color: black;
		background-color: white;
		padding: 15px;
		border-radius: 6px;
		font-size: large;
    }
    .custom-btn:hover {
      background-color: #f8f9fa; /* Change background color on hover */
    }
	.bg-color-products {
		background-color:#F2F2F2;
 ;
	}
</style>
