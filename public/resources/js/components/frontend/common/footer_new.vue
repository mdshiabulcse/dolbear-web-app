<template lang="">
  <div class="mt-2 footer_container">
    <div class="container">
      <div class="row">
        <div class="col-12 col-md-6 col-lg-5">
          <div class="d-flex justify-content-between">
            <img
              src="/images/img/dolbear_logo.png"
              class="footer-logo-new"
              alt="logo"
            />
            <!-- <img :src="settings.footer_logo" class="footer-logo-new" alt="logo"> -->

            <div class="d-flex gap-2 me-4 footer_option">
              <a
                v-if="settings.facebook_link"
                target="_blank"
                :href="settings.facebook_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/Facebook.png"
                  alt="logo"
              /></a>
              <a
                v-if="settings.twitter_link"
                target="_blank"
                :href="settings.twitter_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/pinterest.png"
                  alt="logo"
              /></a>
              <a
                v-if="settings.linkedin_link"
                target="_blank"
                :href="settings.linkedin_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/linkedin.png"
                  alt="logo"
              /></a>
              <a
                v-if="settings.youtube_link"
                target="_blank"
                :href="settings.youtube_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/youtube.png"
                  alt="logo"
              /></a>
            </div>
          </div>
          <div class="mt-2" v-html="settings.about_description"></div>
          <div class="d-flex gap-5">
            <p>{{ settings.footer_contact_address }}</p>

            <p>
              {{ settings.footer_contact_phone }}<br />
              {{ settings.footer_contact_email }}
            </p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-2 footer-menu-new">
          <h4>Quick Links</h4>
          <ul v-for="(link, i) in usefulLinks" :key="i">
            <li>
              <router-link :to="link.url">{{ link.label }}</router-link>
            </li>
          </ul>
        </div>

        <div class="col-12 col-md-6 col-lg-2 footer-menu-new">
          <h4>About US</h4>
          <ul>
            <li v-for="(menu, i) in footerMenu" :key="i">
              <a v-if="urlCheck(menu.url)" :href="menu.url">{{ menu.label }}</a>
              <router-link :to="menu.url">{{ menu.label }}</router-link>
            </li>
          </ul>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <div class="d-flex footer-small-card">
            <img src="/images/img/icon/phone1.png" alt="facebook" />
            <span>|</span>
            <div class="details">
              <span>10am - 8pm</span>
              <p>Helpline</p>
            </div>
          </div>
          <div class="d-flex footer-small-card mt-3">
            <img src="/images/img/icon/store1.png" alt="facebook" />
            <span>|</span>
            <div class="details">
              <span>Store Locator</span>
              <p>Find Our Stores</p>
            </div>
          </div>
          <p class="mt-4">{{ settings.copyright }}</p>
        </div>
      </div>
    </div>

    <div
      class="footer_fixd_section d-flex justify-content-between text-white position-fixed d-sm-block d-md-none"
      style="
        font-family: 'TypoRound', sans-serif;
        bottom: 0;
        height: 75px;
        background-color: rgba(0, 0, 0, 0.8);
        width: 100%;
        z-index: 999;
        padding: 10px 20px;
      "
    >
      <div class=" d-flex flex-column align-items-center text-center">
        <img
          :src="getUrl('images/footer-images/offers.svg')"
          alt="visa_pay_banner"
          class="mb-2"
          width="25px"
          height="25px"
          @click="$router.push('/offer/products')"
        />
        <p>Offers</p>
      </div>

      <div class=" d-flex flex-column align-items-center text-center">
        <img
          :src="getUrl('images/footer-images/bestSeller.svg')"
          alt="visa_pay_banner"
          class="mb-2"
          width="25px"
          height="25px"
          @click="$router.push('/products')"
        />
        <p>Products</p>
      </div>
      <div class=" d-flex flex-column align-items-center text-center">
        <img
          :src="getUrl('images/footer-images/storeLocation.svg')"
          alt="visa_pay_banner"
          class="mb-2"
          width="25px"
          height="25px"
          @click="$router.push('/store')"
        />
        <p>Stores</p>
      </div>
      <div class=" d-flex flex-column align-items-center text-center">
        <a href="tel:+1234567890" style="color: white">
          <img
            :src="getUrl('images/footer-images/phone.svg')"
            alt="phone_icon"
            class="mb-2"
            width="25px"
            height="25px"
          />
          <p>Call/Text</p>
        </a>
      </div>
      <div class=" d-flex flex-column align-items-center text-center">
        <a href="tel:+1234567890" style="color: white">
          <img
            :src="getUrl('images/img/icon/profile.png')"
            alt="phone_icon"
            class="mb-2"
            width="25px"
            height="25px"
          />
          <p>Account</p>
        </a>
      </div>
    </div>
  </div>
</template>
<script>
import chat_system from "../pages/addons/chat_system";

export default {
  name: "bottom",
  components: {
    chat_system,
  },

  data() {
    return {
      gdpr: true,
    };
  },
  mounted() {
    // const scrollToTop = document.querySelector(".btnTOP");
    // const showBTN = function () {
    //   if (window.scrollY > 300) {
    //     scrollToTop.style.opacity = 1;
    //     scrollToTop.style.transform = "translateY(0px)";
    //   } else {
    //     scrollToTop.style.opacity = 0;
    //     scrollToTop.style.transform = "translateY(100px)";
    //   }
    // };
    // document.addEventListener("scroll", () => {
    //   showBTN();
    // });
    // scrollToTop.addEventListener("click", function () {
    //   window.scroll({ top: 0, behavior: "smooth" });
    // });
    // var width = 0;
    // window.onresize = () => {
    //   width = window.innerWidth > 0 ? window.innerWidth : screen.width;
    //   let selector = $(".sg-seller-product .sg-category-content.sg-filter");
    //   let feature_selector = $(".sg-feature-shop .sg-filter");
    //   if (width < 575) {
    //     if (selector.hasClass("list-view-tab")) {
    //       selector.removeClass("list-view-tab");
    //     }
    //     if (feature_selector.hasClass("list-view-tab")) {
    //       feature_selector.removeClass("list-view-tab");
    //     }
    //   } else {
    //     if (selector.hasClass("list_restore")) {
    //       selector.addClass("list-view-tab");
    //     }
    //     feature_selector.addClass("list-view-tab");
    //   }
    // };
  },
  computed: {
    usefulLinks() {
      return this.settings.useful_links;
    },
    footerMenu() {
      return this.settings.footer_menu;
    },
  },
  methods: {
    checkGDPR() {
      return !localStorage.getItem("gdpr") && this.settings.gdpr_enable == 1;
    },
    setGDPR() {
      this.gdpr = false;
      return localStorage.setItem("gdpr", "1");
    },
    classObj() {
      return [
        this.settings.seller_system == 1 && !this.authUser
          ? "col-lg-2"
          : "col-lg-3",
      ];
    },
  },
};
</script>

<style scop>
.footer_container {
  background: black;
  color: white;
  padding: 50px 0;
}

.footer-logo-new {
  height: 75px;
  margin-left: -11px;
  margin-top: -16px;
}

.social-icon {
  height: 40px;
}

.footer_container p {
  font-size: 16px;
  font-weight: 500;
  line-height: 27px;
  text-align: left;
}

.footer-menu-new h4 {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 30px;
}

.footer-menu-new ul {
  padding-left: 0;
}

.footer-menu-new ul li {
  padding: 5px 0;
  font-size: 16px;
}

.footer-menu-new ul li a {
  color: white !important;
}

.footer-small-card {
  background: #ffffff;
  border-radius: 15px;
  padding: 0px 21px;
  align-items: center;
  color: black;
}

.footer-small-card span {
  margin: 0px 15px;
}

.footer-small-card .details span {
  margin-left: 0;
  font-size: 11px;
}

@media screen and (max-width: 768px) {
  .footer-logo-new {
    height: 58px;
    margin-left: -11px;
    margin-top: -11px;
    margin-right: 17px;
}


}
</style>
