<template>
  <div class="sg-page-content container">
    <!-- Expired Campaign Message -->
    <div v-if="campaign && !campaign.is_active_now && campaign.status !== 'upcoming'" class="campaign-expired-notice mt-4 mb-4">
      <div class="alert alert-warning">
        <h4><i class="bx bx-time-five"></i> {{ lang.campaign_ended || 'This Campaign Has Ended' }}</h4>
        <p>{{ lang.campaign_ended_message || 'This campaign is no longer active. Please check back for future deals!' }}</p>
      </div>
    </div>

    <!-- Active Campaign Header -->
    <div v-if="campaign && lengthCounter(productList)>0" class="sg-breadcumb-section">
      <img :src="campaign.banner || campaign.image_1920x412" :alt="campaign.title || campaign.event_title">
    </div>

    <section class="shimmer-section" v-else-if="shimmer">
      <shimmer class="shimmer-rds" :height="412"></shimmer>
    </section>

    <section class="brand-section">
      <div class="container">
        <div class="title title-center" v-if="lengthCounter(productList)>0">
          <h1>{{ campaign.title || campaign.event_title }}</h1>
          <p>{{ campaign.short_description || campaign.description }}</p>

          <!-- Countdown Timer -->
          <div class="sg-countdown" v-if="campaign && (campaign.event_schedule_end || campaign.campaign_end_date)">
            <flip-countdown
              class="countdown"
              :deadline="campaign.event_schedule_end || campaign.end_date"
              v-if="campaign.is_active_now">
            </flip-countdown>
            <div v-else class="expired-timer">
              <span class="badge badge-warning">{{ lang.campaign_expired || 'Campaign Ended' }}</span>
            </div>
          </div>
        </div>

        <div class="title title-center" v-else-if="shimmer">
          <shimmer :height="100"></shimmer>
        </div>

        <!-- Campaign Type Badge -->
        <div v-if="campaign && campaign.campaign_type" class="text-center mb-3">
          <span class="badge badge-info">{{ getCampaignTypeLabel(campaign.campaign_type) }}</span>
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

    <section class="brand-section">
      <div class="container">
        <!-- Campaign Info Section -->
        <div v-if="campaign" class="card mb-4">
          <div class="card-body">
            <h5>{{ lang.campaign_details || 'Campaign Details' }}</h5>
            <div class="row">
              <div class="col-md-6">
                <p><strong>{{ lang.validity || 'Validity' }}:</strong>
                  <span v-if="campaign.event_schedule_start && campaign.event_schedule_end">
                    {{ formatDate(campaign.event_schedule_start) }} - {{ formatDate(campaign.event_schedule_end) }}
                  </span>
                  <span v-else>-</span>
                </p>
                <p><strong>{{ lang.status || 'Status' }}:</strong>
                  <span v-if="campaign.is_active_now" class="badge badge-success">{{ lang.active || 'Active' }}</span>
                  <span v-else-if="campaign.status === 'upcoming'" class="badge badge-info">{{ lang.upcoming || 'Upcoming' }}</span>
                  <span v-else class="badge badge-secondary">{{ lang.ended || 'Ended' }}</span>
                </p>
              </div>
              <div class="col-md-6">
                <p v-if="campaign.badge_text"><strong>{{ lang.offer || 'Offer' }}:</strong>
                  <span class="badge" :style="{ backgroundColor: campaign.badge_color || '#ff0000', color: 'white' }">
                    {{ campaign.badge_text }}
                  </span>
                </p>
                <p v-if="campaign.default_discount"><strong>{{ lang.discount || 'Discount' }}:</strong>
                  {{ campaign.default_discount }}{{ campaign.default_discount_type === 'percentage' ? '%' : ' ' + getSymbol }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import product from "./product";
import FlipCountdown from "vue2-flip-countdown";
import StarRating from '../partials/StarRating.vue';
import shimmer from "../partials/shimmer";
import seller from "../partials/seller";
import product_card from '../common/product_card.vue';

export default {
  name: "campaign_details",
  components: {
    product, FlipCountdown, StarRating, shimmer, seller, product_card
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
      is_shimmer: false,
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
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
.campaign-expired-notice {
  max-width: 800px;
  margin: 0 auto;
}

.expired-timer {
  padding: 20px;
}

.badge {
  padding: 8px 16px;
  font-size: 14px;
}

/* Products Section Grid Layout */
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

/* Mobile - 2 columns by default */
@media screen and (max-width: 576px) {
  .product-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 6px;
  }

  .products-section .row {
    margin: 0 -6px;
  }
}

/* Small mobile - 430px and below */
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

/* Extra small mobile - 360px and below */
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

/* Tablet - 3 columns */
@media screen and (min-width: 577px) and (max-width: 991px) {
  .product-col {
    flex: 0 0 33.333%;
    max-width: 33.333%;
  }
}

/* Desktop - 4 columns */
@media screen and (min-width: 992px) {
  .product-col {
    flex: 0 0 25%;
    max-width: 25%;
  }
}

/* Large Desktop - 5 columns */
@media screen and (min-width: 1400px) {
  .product-col {
    flex: 0 0 20%;
    max-width: 20%;
  }
}
</style>
