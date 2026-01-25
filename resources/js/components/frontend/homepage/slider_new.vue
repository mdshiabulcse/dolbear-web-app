<template>
	<div class="slider-section">
		<!-- main slider action -->
		<section class="container">
			<div class="hero-slider-new" v-if="sliders.length > 0">
				<carousel style="z-index: 0 !important;" :autoplay="true" :loop="true" :nav="false" :margin="0" :items="1" :dots="false"
					:autoplayTimeout="3000" :autoplayHoverPause="true">

					<div class="item" v-for="(image, index) in sliders" :key="index">
						<a :href="image.link" target="_blank" class="slider-link">
              <img :src="image.slider_bg_image" :alt="image.link">
            </a>
					</div>

				</carousel>
			</div>
		</section>
		<!-- <template v-if="popupAds.length > 0">
		<transition name="modal">
			<div class="modal" v-if="isOpen" @click="closeModal">
			<div class="modal-content" @click.stop>
				<carousel
					:autoplay="true"
					:loop = "true"
					:nav = "false"
					:margin = "50"
					:items="1"
					:dots = "false"
					:autoplayTimeout = "3000"
					:autoplayHoverPause = "true"
					>

					<div  class="item" v-for="(popupAd, index) in popupAds" :key="index">
					<img :src="popupAd.image" :alt="popupAd.image">
					</div>

				</carousel>
			</div>
			</div>
		</transition>
		</template> -->
	</div>
</template>

<script>
import VueSlickCarousel from "vue-slick-carousel";
import shimmer from "../partials/shimmer";
import sidebar_categories from "../partials/sidebar_categories";
import carousel from 'vue-owl-carousel';

export default {
	name: "slider",
	components: { VueSlickCarousel, shimmer, sidebar_categories, carousel },
	data() {
		return {
			images: [],
			loading: true,
			popupAds: [],

			isOpen: false,

			slick_settings: {
				dots: true,
				edgeFriction: 0.35,
				infinite: true,
				speed: 500,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				autoplay: true,
				// "fade": true,
				autoplaySpeed: 5000,
			},
		};
	},
	mounted() {

		this.loadImages();
		this.isOpen = true;

	},
	computed: {
		sliders() {
			return this.$store.getters.getSliders;
		},
		banners() {
			return this.$store.getters.getSliderBanners;
		},
	},
	methods: {
		closeModal() {
			// Close the modal when clicked outside
			this.isOpen = false;
		},
		loadImages() {

			const baseUrl = `${window.location.protocol}//${window.location.host}`;

			this.images = [
				{ url: `${baseUrl}/images/final_carosol_image.png`, alt: 'Image 1' },
				{ url: `${baseUrl}/images/final_carosol_image2.jpg`, alt: 'Image 2' },
				{ url: `${baseUrl}/images/final_carosol_image3.jpg`, alt: 'Image 3' },
				{ url: `${baseUrl}/images/final_carosol_image4.jpg`, alt: 'Image 4' },

			];

		},
		toggleCategory() {
			if (this.defaultCategoryShow == false) {
				document.body.classList.add("sidebar-active");
				this.$store.dispatch("defaultCategoryShow", true);
			} else {
				document.body.classList.remove("sidebar-active");
				this.$store.dispatch("defaultCategoryShow", false);
			}
		},
	},
};
</script>

<style scoped>
/* ===== SLIDER SECTION SPACING ===== */
.slider-section {
  margin-top: 20px;
  margin-bottom: 20px;
  width: 100%;
}

/* ===== MOBILE SPACING ===== */
@media (max-width: 575px) {
  .slider-section {
    margin-top: 10px;
    margin-bottom: 15px;
  }
}

@media (min-width: 576px) and (max-width: 767px) {
  .slider-section {
    margin-top: 15px;
    margin-bottom: 15px;
  }
}

/* ===== HERO SLIDER BASE STYLES ===== */
.hero-slider-new {
  width: 100%;
  overflow: hidden;
  position: relative;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Owl Carousel fixes */
.hero-slider-new .owl-stage-outer,
.hero-slider-new .owl-stage {
  height: auto !important;
}

.hero-slider-new .owl-item {
  height: auto !important;
}

/* ===== SLIDER ITEMS ===== */
.item {
  margin: 0 !important;
  padding: 0 !important;
  overflow: hidden;
  border-radius: 8px;
}

.slider-link {
  display: block;
  margin: 0;
  padding: 0;
  width: 100%;
  position: relative;
  border-radius: 8px;
  overflow: hidden;
}

/* ===== IMAGE STYLES - NO CROPPING ===== */
.item img {
  width: 100% !important;
  height: auto !important;
  object-fit: contain !important;
  display: block !important;
  margin: 0 !important;
  padding: 0 !important;
  border: none !important;
  border-radius: 8px;
}

/* ===== MOBILE SPECIFIC FIXES ===== */
@media (max-width: 767px) {
  /* Remove carousel margins on mobile */
  .hero-slider-new .owl-carousel .owl-item {
    padding: 0 !important;
  }

  /* Ensure no overflow on mobile */
  .hero-slider-new .owl-carousel .owl-stage-outer {
    overflow: hidden !important;
  }

  /* Fix touch gestures */
  .hero-slider-new {
    touch-action: pan-y pinch-zoom;
  }

  /* Reduce border-radius on small mobile */
  .hero-slider-new,
  .item,
  .slider-link,
  .item img {
    border-radius: 6px;
  }
}

/* ===== MODAL STYLES ===== */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.5s;
  z-index: 9999;
}

.modal-content {
  background-color: white;
  padding: 20px;
  width: 90%;
  max-width: 900px;
  border-radius: 8px;
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.5s;
}

.modal-enter,
.modal-leave-to {
  opacity: 0;
}
</style>