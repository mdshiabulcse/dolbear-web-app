<template>
    <div class="container">
        <div class=" d-none d-md-flex gap-2">
            <button v-for="(tab, index) in tabs" :key="index"
                :class="['product-specification-btn', { active: activeTab === index }]" @click="activeTab = index">
                {{ tab.name }}
            </button>
        </div>

        <div class="product-specification-content">
            <div v-for="(tab, index) in tabs" :key="index" class="content mt-3">
                <button :key="index" :class="['product-specification-btn content-btn', { active: activeTab === index }]"
                    @click="activeTab = index">
                    {{ tab.name }}
                </button>

                <div v-if="tab.name == 'Specification'">
                    <div v-html="productDetails?.short_description"></div>

                </div>

                <div v-else-if="tab.name === 'Description'">
                    <div v-html="productDetails?.language_product?.description"></div>
                </div>
                <div v-else-if="tab.name === 'Questions'">
                    <div v-html="productDetails?.question"></div>
                </div>

                <div v-else-if="tab.name === 'Review'">
                    <div class="product-details-description">
                        <div class="customer-reviews" style="justify-content: start;">
                            <div class="d-flex">
                                <div class="left-content" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <h2 style="border-radius: 50%; width: 100px; height: 100px">
                                        {{
                                            productDetails.rating > 0
                                                ? productDetails.rating.toFixed(2)
                                                : 0
                                        }}
                                        <small>{{ lang.out_of }} {{ reviews?.total }}</small>
                                    </h2>
                                    <div class="sg-rating">
                                        <star-rating v-model:rating="productDetails.rating" :read-only="true"
                                            :star-size="12" :round-start-rating="false"
                                            class="rating-position"></star-rating>
                                    </div>
                                    <h3>
                                        ({{ productDetails.reviews_count }} {{ lang.reviews }})
                                    </h3>
                                </div>
                                <div class="right-content">
                                    <div class="sg-progress" v-for="(percentage, index) in percentages"
                                        :key="'percentage' + index">
                                        <span>{{ index }} star</span>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar"
                                                :style="'width: ' + percentage + '%'" :aria-valuenow="percentage"
                                                aria-valuemin="0" :aria-valuemax="percentage"></div>
                                        </div>
                                        <strong>{{ percentage }}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="tab.name === 'Video'" class="d-flex gap-2 mt-2">

                    <iframe v-if="productDetails?.video_provider == 'youtube'" width="100%" height="250px"
                        :src="`https://www.youtube.com/embed/${productDetails?.video_link}`" frameborder="0"
                        allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    <!-- <iframe width="100%" height="250px"
                        src="https://www.youtube.com/embed/mFv0tMZHMfA?enablejsapi=1&origin=https%3A%2F%2Fwww.anker.com&widgetid=6"
                        frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    <iframe width="100%" height="250px"
                        src="https://www.youtube.com/embed/mFv0tMZHMfA?enablejsapi=1&origin=https%3A%2F%2Fwww.anker.com&widgetid=6"
                        frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
                </div>

            </div>
        </div>
    </div>
</template>

<script>

import StarRating from "vue-star-rating";

export default {
    props: ["productDetails"],
    components: {
        StarRating,
    },
    data() {
        return {
            activeTab: 0,
            tabs: [
                { name: "Specification" },
                { name: "Description" },
                { name: "Questions" },
                { name: "Review" },
                { name: "Video" },
            ],
            percentages: [],
            reviews: {
                data: [],
                total: 0,
            },
        };
    },

    watch: {
        productDetails() {
            console.log("specification", this.productDetails)
        },

    },
    methods: {
        fetchReviews() {
            if (this.reviews.data.length == 0) {
                let url = this.getUrl("home/product-reviews/" + this.productDetails.id);
                axios
                    .get(url)
                    .then((response) => {
                        if (response.data.error) {
                            toastr.error(response.data.error, this.lang.Error + " !!");
                        } else {
                            this.reviews = response.data.reviews;
                            this.percentages = response.data.percentages;
                        }
                    })
                    .catch((error) => {
                        toastr.error(this.lang.Oops, this.lang.Error + " !!");
                    });
            }
        },
    },
    created() {
        this.fetchReviews();
    },
};
</script>

<style>
.product-specification-btn {
    width: 180px;
    height: 40px;
    border-radius: 5px;
    color: white;
    border: none;
    background-color: #6cc9f0;
    transition: background-color 0.3s;
}

.product-specification-btn.content-btn {
    width: 110px;
    height: 23px;

}

.product-specification-btn.active {
    background: #191919 !important;
    color: white !important;
}

.product-specification-btn:hover {
    background-color: #5bb8e0;
}

@media screen and (max-width: 430px) {
    .product-specification-btn.content-btn {
        width: 140px;
        height: 33px;
    }
}
</style>