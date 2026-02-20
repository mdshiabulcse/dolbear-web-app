<template>
  <div class="sg-page-content">
    <!-- Expired Campaign Message -->
    <div v-if="campaign && !campaign.is_active_now && campaign.status !== 'upcoming'" class="campaign-expired-notice mt-4 mb-4 container">
      <div class="alert alert-warning">
        <h4><i class="bx bx-time-five"></i> {{ lang.campaign_ended || 'This Campaign Has Ended' }}</h4>
        <p>{{ lang.campaign_ended_message || 'This campaign is no longer active. Please check back for future deals!' }}</p>
      </div>
    </div>

    <!-- Active Campaign Header - Responsive Banner -->
    <div class="container">
      <div v-if="campaign && lengthCounter(productList)>0" class="campaign-banner-wrapper">
        <div class="campaign-banner-container">
          <img
              :src="campaign.banner || campaign.image_1920x412"
              :alt="campaign.title || campaign.event_title"
              class="campaign-banner-img"
          >
        </div>
      </div>

      <section class="shimmer-section" v-else-if="shimmer">
        <shimmer class="shimmer-rds banner-shimmer" :height="412"></shimmer>
      </section>
    </div>

    <section class="brand-section">
      <div class="container">
        <div class="title title-center" v-if="lengthCounter(productList)>0">
          <h1>{{ campaign.title || campaign.event_title }}</h1>
          <p>{{ campaign.short_description || campaign.description }}</p>
        </div>

        <div class="title title-center" v-else-if="shimmer">
          <shimmer :height="100"></shimmer>
        </div>

        <!-- Campaign Type Badge -->
        <div v-if="campaign && campaign.campaign_type" class="text-center mb-3">
          <span class="badge badge-dark">{{ getCampaignTypeLabel(campaign.campaign_type) }}</span>
        </div>

        <!-- Navigation Tabs -->
        <div class="sg-menu-2" v-if="lengthCounter(productList)>0 && productList[0] != 'id'">
          <ul class="global-list" role="tablist">
            <li role="presentation" class="nav-item" @click="campaignProducts"
                :class="{'show active' : activeNav == 'products'}">
              <a class="nav-link" href="javaScript:void(0)" aria-controls="products" role="tab" data-bs-toggle="tab">
                {{ lang.products || 'Products' }}
              </a>
            </li>
          </ul>
        </div>

        <div role="tabpanel" class="tab-pane fade" :class="{'show': activeNav == 'products'}" id="products">
          <section class="products-section" v-if="lengthCounter(productList) > 0">
            <div class="row">
              <div v-for="product in products.data" :key="product.id" class="col-6 col-md-4 col-lg-3 product-col">
                <product_card :product="product"></product_card>
              </div>
            </div>
          </section>

          <div class="col-md-12 text-center show-more" v-if="product_next_page_url && !loading">
            <a href="javaScript:void(0)" @click="loadMoreData(product_next_page_url)" class="btn btn-primary">
              {{ lang.show_more || 'Show More' }}
            </a>
          </div>
        </div>

        <div class="col-md-12 text-center show-more" v-show="loading">
          <loading_button :class_name="'btn btn-primary'"></loading_button>
        </div>
      </div><!-- /.container -->
    </section><!-- /.brand-section -->

    <!-- Campaign Details Section -->
    <section class="campaign-details-section">
      <div class="container">
        <!-- Campaign Info Card -->
        <div v-if="campaign" class="campaign-info-card">
          <div class="campaign-header">
            <h3 class="campaign-title">{{ lang.campaign_details || 'Campaign Details' }}</h3>
            <span class="campaign-type-badge">
              {{ getCampaignTypeLabel(campaign.campaign_type) }}
            </span>
          </div>

          <div class="campaign-content">
            <!-- Main Info Grid -->
            <div class="info-grid">
              <!-- Validity Period -->
              <div class="info-item" v-if="campaign.event_schedule_start && campaign.event_schedule_end">
                <div class="info-icon">
                  <i class="bx bx-calendar"></i>
                </div>
                <div class="info-text">
                  <span class="info-label">{{ lang.validity || 'Validity' }}</span>
                  <span class="info-value">
                    {{ formatDate(campaign.event_schedule_start) }} -
                    {{ formatDate(campaign.event_schedule_end) }}
                  </span>
                </div>
              </div>

              <!-- Discount Offer -->
              <div class="info-item" v-if="campaign.default_discount">
                <div class="info-icon">
                  <i class="bx bx-purchase-tag"></i>
                </div>
                <div class="info-text">
                  <span class="info-label">{{ lang.discount_offer || 'Discount Offer' }}</span>
                  <span class="info-value highlight">
                    {{ campaign.default_discount }}{{ campaign.default_discount_type === 'percentage' ? '%' : ' ' + getSymbol }} OFF
                  </span>
                </div>
              </div>

              <!-- Special Badge/Offer -->
              <div class="info-item" v-if="campaign.badge_text">
                <div class="info-icon">
                  <i class="bx bx-gift"></i>
                </div>
                <div class="info-text">
                  <span class="info-label">{{ lang.special_offer || 'Special Offer' }}</span>
                  <span class="info-value badge-text" :style="{ backgroundColor: campaign.badge_color || '#000000', color: '#ffffff' }">
                    {{ campaign.badge_text }}
                  </span>
                </div>
              </div>

              <!-- Products Count -->
              <div class="info-item">
                <div class="info-icon">
                  <i class="bx bx-package"></i>
                </div>
                <div class="info-text">
                  <span class="info-label">{{ lang.products || 'Products' }}</span>
                  <span class="info-value">
                    {{ campaign.total_products || (products && products.data ? products.data.length : 0) }} {{ lang.items || 'Items' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Description Section -->
            <div class="campaign-description" v-if="campaign.description || campaign.short_description">
              <h4 class="description-title">{{ lang.about_campaign || 'About This Campaign' }}</h4>
              <p class="description-text">
                {{ campaign.short_description || campaign.description }}
              </p>
            </div>

            <!-- Terms & Conditions -->
            <div class="campaign-terms" v-if="campaign.terms_conditions">
              <details class="terms-details">
                <summary class="terms-summary">
                  <i class="bx bx-file"></i> {{ lang.terms_conditions || 'Terms & Conditions' }}
                </summary>
                <div class="terms-content" v-html="campaign.terms_conditions"></div>
              </details>
            </div>
          </div>
        </div>

        <!-- Shimmer Loading Effect -->
        <div v-else-if="shimmer" class="campaign-info-card shimmer">
          <div class="campaign-header">
            <shimmer :height="30" :width="200"></shimmer>
          </div>
          <div class="campaign-content">
            <shimmer :height="80" class="mb-3"></shimmer>
            <shimmer :height="60" class="mb-3"></shimmer>
            <shimmer :height="40"></shimmer>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import product from "./product";
import StarRating from '../partials/StarRating.vue';
import shimmer from "../partials/shimmer";
import seller from "../partials/seller";
import product_card from '../common/product_card.vue';

export default {
  name: "campaign_details",
  components: {
    product, StarRating, shimmer, seller, product_card
  },
  data() {
    return {
      activeClass: "",
      skip: 1,
      show_load_more: true,
      active: 0,
      loading: false,
      page: 1,
      activeNav: "products",
      product_next_page_url: false,
      brand_next_page_url: false,
      shop_next_page_url: false,
      url: "",
      fetched_campaign: '',
      checkListing: true,
      is_shimmer: false
    }
  },
  mounted() {
    this.campaignProducts();
    if (this.lengthCounter(this.shops) > 0) {
      this.is_shimmer = true;
    }
  },
  computed: {
    baseUrl() {
      return this.$store.getters.getBaseUrl;
    },
    products() {
      let products = this.$store.getters.getCampaignProducts;
      for (let i = 0; i < products.length; i++) {
        if (products[i].slug == this.$route.params.slug) {
          return products[i].products;
        }
      }
      return [];
    },
    brands() {
      let brands = this.$store.getters.getCampaignBrands;
      for (let i = 0; i < brands.length; i++) {
        if (brands[i].slug == this.$route.params.slug) {
          return brands[i].brands;
        }
      }
      return [];
    },
    shops() {
      let shops = this.$store.getters.getCampaignShops;
      for (let i = 0; i < shops.length; i++) {
        if (shops[i].slug == this.$route.params.slug) {
          return shops[i].shops;
        }
      }
      return [];
    },
    campaign() {
      return this.fetched_campaign;
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    productList() {
      if (this.products && this.products.data && this.products.data.length == 0) {
        return ['id'];
      } else if (this.products && this.products.data && this.products.data.length > 0) {
        return this.products.data;
      } else {
        return [];
      }
    },
    getSymbol() {
      return this.$store.getters.getCurrencySymbol;
    }
  },
  methods: {
    getCampaignTypeLabel(type) {
      const labels = {
        'product': this.lang.product_based || 'Product-based',
        'category': this.lang.category_based || 'Category-based',
        'brand': this.lang.brand_based || 'Brand-based',
        'event': this.lang.event_based || 'Event-based'
      };
      return labels[type] || type;
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    campaignProducts() {
      let requestData = {
        slug: this.$route.params.slug,
      };
      this.activeNav = 'products'

      let url = this.baseUrl + '/home/campaign-products';

      if (this.lengthCounter(this.products) > 0) {
        this.product_next_page_url = this.products.next_page_url;
        let found = this.$store.getters.getCampaignStore.filter(val => val.slug == this.$route.params.slug);
        if (found){
          this.fetched_campaign = found[0];
        }
        return this.products;
      }

      axios.get(url, {params: requestData}).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
        this.fetched_campaign = response.data.campaign;
        this.$store.commit('setCampaignStore', response.data.campaign);

        this.product_next_page_url = response.data.products.next_page_url;
        let data = {
          slug: this.$route.params.slug,
          products: response.data.products
        };
        this.$store.commit('getCampaignProducts', data);
      })
    },
    loadMoreData(url, type) {
      let requestData = {
        slug: this.$route.params.slug,
        type: type,
      };
      this.loading = true
      axios.get(url, {params: requestData}).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.loading = false;
          if (response.data.products) {
            this.product_next_page_url = response.data.products.next_page_url;
            let data = {
              slug: this.$route.params.slug,
              products: response.data.products
            };
            this.$store.commit('getCampaignProducts', data);
          } else if (response.data.brands) {
            let data = {
              slug: this.$route.params.slug,
              brands: response.data.brands
            };
            this.brand_next_page_url = response.data.brands.next_page_url;
            this.$store.commit('getCampaignBrands', data);
          } else if (response.data.shops) {
            let data = {
              slug: this.$route.params.slug,
              shops: response.data.shops
            };
            this.shop_next_page_url = response.data.shops.next_page_url;
            this.$store.commit('getCampaignShops', data);
          }
        }
      });
    }
  }
}
</script>

<style scoped>
/* Expired Campaign Notice */
.campaign-expired-notice {
  max-width: 800px;
  margin: 20px auto;
}

.badge-dark {
  background-color: #000000;
  color: #ffffff;
  padding: 8px 16px;
  font-size: 14px;
  border-radius: 4px;
}

/* Banner Styles - Container width only */
.campaign-banner-wrapper {
  width: 100%;
  margin-top: 30px;
  margin-bottom: 30px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
}

.campaign-banner-container {
  width: 100%;
  height: auto;
  background-color: #f5f5f5;
}

.campaign-banner-img {
  width: 100%;
  height: auto;
  display: block;
}

/* Banner Shimmer */
.banner-shimmer {
  width: 100%;
  margin-top: 30px;
  margin-bottom: 30px;
  border-radius: 8px;
  overflow: hidden;
}

/* Responsive Banner Adjustments */
@media (max-width: 768px) {
  .campaign-banner-wrapper {
    margin-top: 20px;
    margin-bottom: 20px;
  }
  .banner-shimmer {
    margin-top: 20px;
  }
}

@media (max-width: 576px) {
  .campaign-banner-wrapper {
    margin-top: 15px;
    margin-bottom: 15px;
  }
  .banner-shimmer {
    margin-top: 15px;
  }
}

/* Campaign Details Section Styles */
.campaign-details-section {
  padding: 0 0 40px;
  background-color: #ffffff;
}

.campaign-info-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  border: 1px solid #eaeaea;
}

.campaign-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background-color: #000000;
  color: #ffffff;
  border-bottom: 1px solid #333333;
}

.campaign-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #ffffff;
}

.campaign-type-badge {
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  background-color: #333333;
  color: #ffffff;
}

.campaign-content {
  padding: 20px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8f8f8;
  border-radius: 6px;
  border: 1px solid #eaeaea;
}

.info-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #000000;
  border-radius: 6px;
  color: #ffffff;
  font-size: 1.1rem;
}

.info-text {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.75rem;
  color: #666666;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 2px;
}

.info-value {
  font-size: 1rem;
  font-weight: 600;
  color: #000000;
}

.info-value.highlight {
  color: #28a745;
}

.badge-text {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 500;
}

.campaign-description {
  margin-bottom: 20px;
  padding: 16px;
  background: #f8f8f8;
  border-radius: 6px;
  border-left: 3px solid #000000;
}

.description-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #000000;
}

.description-text {
  color: #333333;
  line-height: 1.5;
  margin: 0;
  font-size: 0.95rem;
}

.campaign-terms {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eaeaea;
}

.terms-summary {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #000000;
  font-weight: 500;
  font-size: 0.95rem;
  list-style: none;
}

.terms-summary::-webkit-details-marker {
  display: none;
}

.terms-summary i {
  font-size: 1.1rem;
}

.terms-content {
  margin-top: 12px;
  padding: 12px;
  background: #f8f8f8;
  border-radius: 6px;
  color: #333333;
  font-size: 0.9rem;
  line-height: 1.5;
  border: 1px solid #eaeaea;
}

/* Products Section */
.products-section {
  margin-top: 20px;
}

.products-section .row {
  margin: 0 -8px;
}

.product-col {
  padding: 8px;
  margin-bottom: 16px;
}

/* Mobile Responsive */
@media screen and (max-width: 576px) {
  .product-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 6px;
  }

  .products-section .row {
    margin: 0 -6px;
  }

  .campaign-header {
    flex-direction: column;
    text-align: center;
    gap: 8px;
    padding: 12px;
  }

  .campaign-title {
    font-size: 1.1rem;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .info-item {
    padding: 10px;
  }

  .campaign-content {
    padding: 15px;
  }
}

@media screen and (max-width: 430px) {
  .product-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 4px;
  }

  .products-section .row {
    margin: 0 -4px;
  }
}

@media screen and (max-width: 360px) {
  .product-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 3px;
  }

  .products-section .row {
    margin: 0 -3px;
  }
}

/* Tablet */
@media screen and (min-width: 577px) and (max-width: 991px) {
  .product-col {
    flex: 0 0 33.333%;
    max-width: 33.333%;
  }
}

/* Desktop */
@media screen and (min-width: 992px) {
  .product-col {
    flex: 0 0 25%;
    max-width: 25%;
  }
}

/* Large Desktop */
@media screen and (min-width: 1400px) {
  .product-col {
    flex: 0 0 20%;
    max-width: 20%;
  }
}
</style>