<template>
    <div class="sg-page-content">
        <section class="campaign-section">
            <div class="container">
                <div class="title justify-content-between">
                    <h1 v-if="is_shimmer">
                        {{ lengthCounter(campaigns) > 0 ? lang.campaigns : lang.no_campaign_founds }}
                    </h1>
                    <h1 v-else>{{ lang.content_loading }}</h1>
                </div>

                <!-- Active Campaign Notice -->
                <div v-if="activeCampaign" class="alert alert-success mb-4">
                    <h4><i class="bx bx-tag"></i> {{ lang.active_campaign || 'Active Campaign' }}</h4>
                    <p>{{ lang.active_campaign_message || 'Special offers available!' }}</p>
                </div>

                <div class="row" v-if="is_shimmer">
                    <div class="col-md-4" v-for="(campaign,index) in campaigns" :key="index">
                        <div class="campaign campaign-style-1">
                            <a :href="getUrl('campaign/'+campaign.slug)"
                               @click.prevent="routerNavigator('campaign.details',campaign.slug)">
                                <img loading="lazy" :src="campaign.image_374x374 || campaign.image_1920x412"
                                     :alt="campaign.event_title || campaign.title"
                                     class="img-fluid">
                            </a>
                            <span v-if="campaign.event_schedule_start && campaign.event_schedule_end">
                                <span class="icon mdi mdi-calendar-month"></span>
                                {{ formatDate(campaign.event_schedule_start) }} - {{ formatDate(campaign.event_schedule_end) }}
                            </span>
                            <h2 class="campaign_title">
                                <router-link :to="{ name : 'campaign.details',params : { slug : campaign.slug } }">
                                    {{ campaign.event_title || campaign.title }}
                                </router-link>
                            </h2>
                            <p>{{ campaign.description || campaign.short_description }}</p>
                            <a :href="getUrl('campaign/'+campaign.slug)"
                               class="btn btn-primary"
                               @click.prevent="routerNavigator('campaign.details',campaign.slug)">
                                {{ lang.get_discount || 'Get Discount' }}
                            </a>
                        </div>
                    </div>
                </div><!-- /.row -->

                <div class="row" v-else-if="shimmer">
                    <div class="col-md-4" v-for="(campaign,index) in 6" :key="index">
                        <shimmer class="mb-3" :height="576"></shimmer>
                    </div>
                </div><!-- /.row -->

                <div v-if="lengthCounter(campaigns) === 0 && is_shimmer" class="col-12 text-center py-5">
                    <i class="bx bx-tag-x" style="font-size: 64px; color: #ccc;"></i>
                    <h4 class="mt-3">{{ lang.no_active_campaign || 'No Active Campaigns' }}</h4>
                    <p>{{ lang.no_active_campaign_message || 'Check back later for exciting deals!' }}</p>
                </div>

                <div class="col-md-12 text-center show-more" v-if="paginate && !loading">
                    <a href="javaScript:void(0)" @click="loadMoreData()" class="btn btn-primary">
                        {{ lang.show_more }}
                    </a>
                </div>
                <div class="col-md-12 text-center show-more" v-show="loading">
                    <loading_button :class_name="'btn btn-primary'"></loading_button>
                </div>
            </div><!-- /.container -->
        </section><!-- /.campaign-section -->
    </div>
</template>

<style scoped>
/* Campaign Cards Responsive Grid */
@media screen and (max-width: 576px) {
    .row [class*="col-"] {
        padding: 8px;
    }
}

@media screen and (max-width: 430px) {
    .row [class*="col-"] {
        padding: 6px;
    }
}

@media screen and (max-width: 360px) {
    .row [class*="col-"] {
        padding: 4px;
    }
}

/* Campaign Card Styles */
.campaign {
    height: 100%;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.campaign:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.campaign img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.campaign:hover img {
    transform: scale(1.05);
}

.campaign_title {
    font-size: 16px;
    font-weight: 600;
    margin: 10px 0;
    line-height: 1.4;
}

.campaign_title a {
    color: #333;
    text-decoration: none;
}

.campaign_title a:hover {
    color: #1BADEB;
}

.campaign p {
    font-size: 13px;
    color: #666;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive Typography */
@media screen and (max-width: 576px) {
    .campaign_title {
        font-size: 14px;
    }

    .campaign p {
        font-size: 12px;
    }

    .campaign img {
        height: 150px;
    }
}

@media screen and (max-width: 360px) {
    .campaign_title {
        font-size: 13px;
    }

    .campaign p {
        font-size: 11px;
    }

    .campaign img {
        height: 130px;
    }
}
</style>

<script>
import shimmer from "../partials/shimmer";

export default {
    name: "all_campaigns",
    data() {
        return {
            loading: false,
            is_shimmer: false,
            activeCampaign: null
        }
    },
    components: {
        shimmer
    },
    mounted() {
        if (this.lengthCounter(this.campaigns) == 0) {
            this.loadActiveCampaign();
            this.loadMoreData();
        }
        if (this.lengthCounter(this.campaigns) > 0) {
            this.is_shimmer = true
        }
    },
    computed: {
        baseUrl() {
            return this.$store.getters.getBaseUrl;
        },
        campaigns() {
            return this.$store.getters.getAllCampaign;
        },
        shimmer() {
            return this.$store.state.module.shimmer
        },
        paginate(){
            return this.$store.state.module.campaign_paginate_url;
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        loadActiveCampaign() {
            // Load the currently active campaign
            axios.get(this.baseUrl + '/api/v100/campaign/active').then((response) => {
                if (response.data.campaign) {
                    this.activeCampaign = response.data.campaign;
                }
            }).catch((error) => {
                console.log('No active campaign');
            });
        },
        loadMoreData() {
            if (this.$store.state.module.campaign_paginate_page != 1) {
                this.loading = true;
            }
            let url = this.baseUrl + '/home/campaign-lists?page=' + this.$store.state.module.campaign_paginate_page;
            this.$Progress.start();
            axios.get(url).then((response) => {
                this.is_shimmer = true
                if (response.data.error) {
                    toastr.error(response.data.error, this.lang.Error + ' !!');
                } else {
                    this.loading = false;
                    this.$store.commit('getAllCampaign', response.data.campaigns);
                    this.$Progress.finish();
                }
            }).catch((error) => {
                this.is_shimmer = true
                this.loading = false;
                this.$Progress.fail();
                if (error.response && error.response.status == 422) {
                    toastr.error(response.data.error, this.lang.Error + ' !!');
                }
            });
        }
    }
}
</script>
