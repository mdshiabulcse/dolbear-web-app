<template>
    <div class="container">
        <div class=" d-none d-md-flex gap-2">
            <button v-for="(tab, index) in tabs" :key="index"
                :class="['product-specification-btn', { active: activeTab === index }]" @click="scrollToSection(index)">
                {{ tab.name }}
            </button>
        </div>

        <div class="product-specification-content">
            <div v-for="(tab, index) in tabs" :key="index" class="content mt-3" :id="'section-' + index">
                <button :key="index" :class="['product-specification-btn content-btn', { active: activeTab === index }]"
                    @click="scrollToSection(index)">
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

                <div class="products-description" v-else-if="tab.name === 'Review'">
                    <div class="product-details-description">
                      <div class="customer-reviews">
                        <div class="d-flex">
                          <div class="left-content">
                            <h2>
                              {{
                                productDetails.rating > 0
                                    ? productDetails.rating.toFixed(2)
                                    : 0
                              }}
                              <small>{{ lang.out_of }} {{ reviews.total }}</small>
                            </h2>
                            <div class="sg-rating">
                              <star-rating
                                  v-model:rating="productDetails.rating"
                                  :read-only="true"
                                  :star-size="12"
                                  :round-start-rating="false"
                                  class="rating-position"
                              ></star-rating>
                            </div>
                            <h3>
                              ({{ productDetails.reviews_count }} {{ lang.reviews }})
                            </h3>
                          </div>
                          <div class="right-content">
                            <div
                                class="sg-progress"
                                v-for="(percentage, index) in percentages"
                                :key="'percentage' + index"
                            >
                              <span>{{ index }} star</span>
                              <div class="progress">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    :style="'width: ' + percentage + '%'"
                                    :aria-valuenow="percentage"
                                    aria-valuemin="0"
                                    :aria-valuemax="percentage"
                                ></div>
                              </div>
                              <strong>{{ percentage }}%</strong>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="sg-reviews">
                        <h2>{{ lang.customer_reviews }}</h2>
                        <h2>{{ reviews.total }} {{ lang.comments }}</h2>
                        <ul class="comment-list global-list">
                          <li
                              v-for="(review, index) in reviews.data"
                              :key="'review' + index"
                          >
                            <div class="comment_info">
                              <div class="commenter-avatar" v-if="review.user">
                                  <img
                                      class="img-fluid"
                                      v-if="review.user.profile_image"
                                      loading="lazy"
                                      :src="review.user.profile_image"
                                      :alt="review.user.full_name"
                                  />
                              </div>
                              <div class="comment-box">
                                <div class="comment-title" v-if="review.user">
                          <span class="title-1">
                            {{ review.user.full_name }}
                          </span>
                                  <div class="sg-rating">
                                    <star-rating
                                        :rating="review.rating"
                                        :read-only="true"
                                        :star-size="10"
                                        active-color="#C9151B"
                                    ></star-rating>
                                  </div>
                                  <div class="comment-meta">
                                    <ul class="global-list">
                                      <li>
                                        <a href="javascript:void(0)">{{
                                            review.review_date
                                          }}</a>
                                      </li>
                                    </ul>
                                  </div>
                                  <a
                                      class="float-end"
                                      v-if="authUser && review.user_id == authUser.id"
                                      @click="editReview(review)"
                                      href="javascript:void(0)"
                                  >{{ lang.edit }}</a
                                  >
                                </div>
                              </div>
                              <!-- /.comment-box -->
                              <h5>{{ review.title }}</h5>
                              <p>{{ review.comment }}</p>
                              <div class="selected-media mt-0 m-2" v-if="review.images">
                                <img
                                    loading="lazy"
                                    :src="review.image_link"
                                    :alt="productDetails.product_name"
                                    class="img-thumbnail"
                                    width="100"
                                />
                              </div>
                              <div class="comment-icon">
                                <ul class="global-list" v-if="authUser">
                                  <li v-if="alreadyLiked(review.review_likes)">
                                    <a
                                        href="javascript:void(0)"
                                        @click="unLike(review.id)"
                                        :class="{ disable_btn: like_loading }"
                                    >
                                      <span class="mdi mdi-thumb-up"></span>
                                      <span class="replies_text"
                                      >({{
                                          review.review_likes
                                              ? review.review_likes.length
                                              : 0
                                        }})</span
                                      ></a
                                    >
                                  </li>

                                  <li v-else>
                                    <a
                                        href="javascript:void(0)"
                                        @click="reviewLike(review.id)"
                                        :class="{ disable_btn: like_loading }"
                                    >
                                      <span class="mdi mdi-thumb-up-outline"></span>
                                      <span class="replies_text"
                                      >({{
                                          review.review_likes
                                              ? review.review_likes.length
                                              : 0
                                        }})</span
                                      ></a
                                    >
                                  </li>

                                  <li>
                                    <a
                                        href="javascript:void(0)"
                                        @click="toggleReplyForm(review.id)"
                                    ><span class="mdi mdi-share"></span
                                    ></a>
                                  </li>
                                  <li v-if="review.replies.length > 0">
                                    <a
                                        href="javascript:void(0)"
                                        @click="showReplies(review.id)"
                                        class="font_18"
                                    >{{ review.replies.length }} {{ lang.replies }}</a
                                    >
                                  </li>
                                </ul>
                              </div>
                              <form
                                  @submit.prevent="reviewReply(review.id)"
                                  v-if="reply_form == review.id"
                              >
                                <div class="row">
                                  <div class="col-lg-1 pr-0"></div>
                                  <div class="col-lg-9 pl-0">
                            <textarea
                                v-model="product_form.reply"
                                class="form-control reply_box"
                                required="required"
                                rows="2"
                            ></textarea>
                                  </div>
                                  <div class="col-lg-2">
                                    <input
                                        v-if="!reply_loading"
                                        type="submit"
                                        class="btn btn-primary"
                                        value="Send"
                                    />
                                    <loading_button
                                        v-else
                                        :class_name="'btn btn-primary'"
                                    ></loading_button>
                                  </div>
                                </div>
                              </form>
                              <ul
                                  class="children global-list"
                                  v-if="review.replies && replies == review.id"
                              >
                                <li
                                    v-for="(reply, index) in review.replies"
                                    :key="'reply' + index"
                                >
                                  <div class="comment_info">
                                    <div class="commenter-avatar" v-if="reply.user">
                                      <router-link :to="{ name: 'dashboard' }"
                                      ><img
                                          class="img-fluid"
                                          loading="lazy"
                                          :src="reply.user.profile_image"
                                          :alt="reply.user.full_name"
                                      /></router-link>
                                    </div>
                                    <div class="comment-box">
                                      <div class="comment-title">
                                <span class="title-1" v-if="reply.user"
                                ><router-link
                                    :to="{ name: 'dashboard' }"
                                    class="url"
                                >{{ reply.user.full_name }}</router-link
                                ></span
                                >
                                        <div class="comment-meta">
                                          <ul class="global-list">
                                            <li>
                                              <a href="javascript:void(0)">{{
                                                  reply.reply_date
                                                }}</a>
                                            </li>
                                          </ul>
                                        </div>
                                      </div>
                                    </div>
                                    <!-- /.comment-box -->
                                    <p>{{ reply.reply }}</p>
                                  </div>
                                </li>
                              </ul>
                            </div>
                          </li>
                        </ul>
                        <div class="text-center show-more" v-if="reviews.next_page_url">
                          <a
                              href="javascript:void(0)"
                              @click="loadReviews()"
                              class="btn btn-primary"
                          >{{ lang.show_more }}</a
                          >
                        </div>
                      </div>

                      <div
                          v-if="(authUser && !productDetails.user_review) || edit"
                          class="review-form"
                      >
                        <h2>{{ lang.write_your_own_review }}</h2>
                        <div class="sg-rating">
                          <div class="rating">
                            <star-rating
                                v-model:rating="product_form.rating"
                                :star-size="20"
                                active-color="#C9151B"
                            ></star-rating>
                          </div>
                        </div>
                        <form @submit.prevent="submitReview">
                          <div class="form-group">
                            <label>{{ lang.review_title }}</label>
                            <input
                                class="form-control"
                                v-model="product_form.title"
                                name="name"
                                type="text"
                                value=""
                                size="30"
                                aria-required="true"
                                required="required"
                            />
                          </div>
                          <div class="form-group">
                            <label>{{ lang.comment }}</label>
                            <textarea
                                name="message"
                                v-model="product_form.comment"
                                class="form-control"
                                required="required"
                                rows="7"
                            ></textarea>
                          </div>
                          <div class="form-group">
                            <label>{{ lang.upload_image }}</label>
                            <div class="input-group">
                              <div class="custom-file d-flex">
                                <label class="upload-image form-control" for="upload-1">
                                  <input
                                      type="file"
                                      id="upload-1"
                                      @change="imageUp($event)"
                                  />
                                  <i ref="imageUpload" id="upload-image">{{
                                      product_form.image_text
                                    }}</i>
                                </label>
                                <label
                                    class="upload-image upload-text d-flex loader-bdr"
                                    for="upload-2"
                                >
                                  <input
                                      type="file"
                                      id="upload-2"
                                      @change="imageUp($event)"
                                  />
                                  <img
                                      loading="lazy"
                                      :src="getUrl('images/others/env.svg')"
                                      :alt="productDetails.product_name"
                                      class="img-fluid"
                                  />
                                  {{ lang.upload }}
                                </label>
                              </div>
                            </div>
                          </div>
                          <input
                              v-if="!review_loading"
                              type="submit"
                              class="btn btn-primary"
                              :value="lang.send"
                          />
                          <loading_button
                              v-else
                              :class_name="'btn btn-primary'"
                          ></loading_button>
                        </form>
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

import StarRating from "../partials/StarRating.vue";

export default {
    props: ["productDetails"],
    components: {
        StarRating,
    },
    data() {
        return {
          activeNav: "details",
          hoveredReview: 0,
          reply_form: 0,
          replies: 0,
          paginate: 1,
          edit: false,
          review_loading: false,
          like_loading: false,
          reply_loading: false,

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
      $route() {
        let set_params = {
          slug: this.$route.params.slug,
          referral_code: this.$route.query.referral_code,
          trx_id: this.carts && this.carts.length > 0 ? this.carts[0].trx_id : '',
        }
        if (!this.productDetails) {

          this.$store.dispatch('productDetails', set_params);
        }
      },
      productDetails(newVal) {
        if (newVal) {
          this.fetchReviews();
        }
      }


    },
    methods: {
        scrollToSection(index) {
            this.activeTab = index;
            this.$nextTick(() => {
                const element = document.getElementById('section-' + index);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
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

      loadReviews() {
        this.paginate++;
        let url = this.getUrl(
            "home/product-reviews/" +
            this.productDetails.id +
            "?page=" +
            this.paginate
        );
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            let reviews = response.data.reviews.data;

            if (reviews.length > 0) {
              for (let i = 0; i < reviews.length; i++) {
                this.reviews.data.push(reviews[i]);
              }
            }
          }
          this.reviews.next_page_url = response.data.reviews.next_page_url;
        });
      },
      reviewLike(id) {
        let data = {
          paginate: this.paginate,
          id: id,
          product_id: this.productDetails.id,
        };
        this.like_loading = true;
        let url = this.getUrl("product/like-review");
        axios
            .post(url, data)
            .then((response) => {
              this.like_loading = false;

              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
              } else {
                if (response.data.success) {
                  toastr.success(response.data.success, this.lang.Success + " !!");
                }
                this.reviews.data = response.data.reviews.data;
                this.reviews.next_page_url = response.data.reviews.next_page_url;
                this.reviews.total = response.data.reviews.total;
              }
            })
            .catch((error) => {
              this.like_loading = false;
            });
      },
      unLike(id) {
        let data = {
          paginate: this.paginate,
          id: id,
          product_id: this.productDetails.id,
        };
        this.like_loading = true;

        let url = this.getUrl("product/unlike-review");
        axios
            .post(url, data)
            .then((response) => {
              this.like_loading = false;

              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
              } else {
                if (response.data.success) {
                  toastr.success(response.data.success, this.lang.Success + " !!");
                }
                this.reviews.data = response.data.reviews.data;
                this.reviews.next_page_url = response.data.reviews.next_page_url;
                this.reviews.total = response.data.reviews.total;
              }
            })
            .catch((error) => {
              this.like_loading = false;
            });
      },
      editReview(review) {
        this.edit = true;
        this.product_form.image_text = ''
        this.product_form.product_id = this.productDetails.id;
        this.product_form.rating = review.rating;
        this.product_form.title = review.title;
        this.product_form.comment = review.comment;
        let file_name = review.image_link;
        if (file_name) {
          let array = file_name.split("/");
          this.product_form.image_text = array[array.length - 1];
        }
      },
      imageUp(event) {
        this.product_form.image = event.target.files[0];
        document.getElementById("upload-image").innerHTML =
            this.product_form.image.name;
      },

      toggleReplyForm(review_id) {
        if (this.reply_form == review_id) {
          this.reply_form = 0;
        } else {
          this.reply_form = review_id;
        }
      },
      showReplies(review_id) {
        if (this.replies == review_id) {
          this.replies = 0;
        } else {
          this.replies = review_id;
        }
      },
      reviewReply(review_id) {
        this.reply_loading = true;
        this.product_form.review_id = review_id;
        this.product_form.product_id = this.productDetails.id;
        this.product_form.paginate = this.paginate;
        let url = this.getUrl("home/product-reply-store");
        axios
            .post(url, this.product_form)
            .then((response) => {
              this.reply_loading = false;
              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
              } else {
                toastr.success(response.data.success, this.lang.Success + " !!");
                this.resetForm();
                this.reviews.data = response.data.reviews.data;
                this.reviews.next_page_url = response.data.reviews.next_page_url;
                this.reviews.total = response.data.reviews.total;
              }
            })
            .catch((error) => {
              this.reply_loading = false;
            });
      },

      submitReview() {
        if (this.product_form.rating == 0) {
          return toastr.error(
              this.lang.choose_a_rating_star_first,
              this.lang.Error + " !!"
          );
        }
        this.review_loading = true;
        this.product_form.product_id = this.productDetails.id;
        this.product_form.paginate = this.paginate;
        let url = this.getUrl("user/product-review-store");
        axios
            .post(url, this.product_form, {
              transformRequest: [
                function (data, headers) {
                  return objectToFormData(data);
                },
              ],
            })
            .then((response) => {
              this.review_loading = false;
              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
              } else {
                toastr.success(response.data.success, this.lang.Success + " !!");
                this.resetForm();
                this.reviews = response.data.reviews;
                this.edit = false;
                this.percentages = response.data.percentages;
                this.$store.dispatch("productDetails", this.$route.params.slug);
                this.productDetails.user_review = true;
              }
            })
            .catch((error) => {
              this.review_loading = false;
            });
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
    margin: 10px 0;
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