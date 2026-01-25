<template lang="">
  <div class="mt-2 footer_container">
    <div class="container">
      <div class="row">
        <div class="col-12 col-md-6 col-lg-5">
          <div class="d-flex justify-content-between flex-column flex-md-row ">
            <img
              src="/images/img/dolbear_logo.png"
              class="footer-logo-new"
              alt="logo"

            />
            <!-- <img :src="settings.footer_logo" class="footer-logo-new" alt="logo"> -->
            <div class="d-flex gap-2 me-4 p-2 p-md-0">
              <a
                v-if="settings.facebook_link"
                target="_blank"
                :href="settings.facebook_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/Facebook.png"
                  alt="logo"
                  style = "width: 30px; height: 30px"
              /></a>
              <a
                v-if="settings.twitter_link"
                target="_blank"
                :href="settings.twitter_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/ticktop_icon.png"
                  alt="logo"
                  style = "width: 30px; height: 30px"
              /></a>
              <a
                v-if="settings.instagram_link"
                target="_blank"
                :href="settings.instagram_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/instragram_icon.png"
                  alt="logo"
                  style = "width: 30px; height: 30px"
              /></a>
              <a
                v-if="settings.linkedin_link"
                target="_blank"
                :href="settings.linkedin_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/linkedin.png"
                  alt="logo"
                  style = "width: 30px; height: 30px"
              /></a>
              <a
                v-if="settings.youtube_link"
                target="_blank"
                :href="settings.youtube_link"
                ><img
                  class="social-icon"
                  src="/images/img/icon/youtube.png"
                  alt="logo"
                  style = "width: 30px; height: 30px"
              /></a>
            </div>
          </div>
          <div class="mt-2 w-75" v-html="settings.about_description"></div>
          <div class="d-flex gap-5">
            <div> 
              <p>{{ settings.footer_contact_address }}</p>
            </div>

            <p>
              {{ settings.footer_contact_phone }}<br />
              {{ settings.footer_contact_email }} 
             
            </p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-2 footer-menu-new" >
          <h4>Quick Links</h4>
          <ul v-for="(link, i) in usefulLinks" :key="i">
            <li  >
              <router-link :to="link.url" >{{ link.label }}</router-link>
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
          <a :href="`tel:${cleanContactPhone}`" class="d-flex footer-small-card phone-card">
            <img src="/images/img/icon/phone1.png" alt="phone" />
            <span>|</span>
            <div class="details">
              <span class="default-text">{{ settings.available_time }}</span>
              <span class="hover-text">{{ settings.footer_contact_phone }}</span>
              <p class="mobile_helpline">Helpline</p>
            </div>
          </a>
          <router-link to="/outlets" class="d-flex footer-small-card store-card mt-3">
            <img src="/images/img/icon/store1.png" alt="store" />
            <span>|</span>
            <div class="details">
              <span>Store Locator</span>
              <p class="mobile_helpline">Find Our Stores</p>
            </div>
          </router-link>
          <p class="mt-4">{{ settings.copyright }}</p>
        </div>
      </div>
    </div>

    <div
      class="footer_fixd_section d-flex justify-content-between text-white position-fixed d-sm-block d-md-none"
      style="
        font-family: 'TypoRound', sans-serif;
        bottom: 0;
        height: 50px;
        background-color: black;
        width: 100%;
        z-index: 999;
        padding: 1px 20px;
      "
    >


      <div class=" d-flex flex-column align-items-center text-center" style = "margin-top: 6px" >
        <img
          :src="getUrl('images/footer-images/offers.svg')"
          alt="visa_pay_banner"
          class="mb-0"
          width="16px"
            height="16px"
          @click="$router.push('/offer/products')"
        />
        <p>Offers</p>
      </div>

      <div class=" d-flex flex-column align-items-center text-center" style = "margin-top: 7px" >
        <img
          :src="getUrl('images/footer-images/bestSeller.svg')"
          alt="visa_pay_banner"
          class="mb-0"
          width="16px"
            height="16px"
          @click="$router.push('/products')"
        />
        <p>Products</p>
      </div>
      <div class=" d-flex flex-column align-items-center text-center" style = "margin-top: 7px" >
        <img
          :src="getUrl('images/footer-images/storeLocation.svg')"
          alt="visa_pay_banner"
          class="mb-0"
          width="16px"
            height="16px"
          @click="$router.push('/store')"
        />
        <p>Stores</p>
      </div>
      <div class=" d-flex flex-column align-items-center text-center">
        <a :href="`tel:${cleanFooterPhone}`" style="color: white">
          <img
            :src="getUrl('images/footer-images/phone.svg')"
            alt="phone_icon"
            class="mb-0"
           width="16px"
            height="16px"
          />
          <p class = "mobile_navbar_text" >Call/Text</p>
        </a>
      </div>
      <div class=" d-flex flex-column align-items-center text-center">

          <router-link  style="color: white" v-if="authUser && authUser?.user_type === 'customer'" :to="{ name: 'dashboard' }">

          <img
            :src="getUrl('images/img/icon/profile.png')"
            alt="phone_icon"
            class="mb-0"
            width="16px"
            height="16px"
          />
          <p class = "mobile_navbar_text">Account</p>
        </router-link>
        <router-link  style="color: white" v-else :to="{ name: 'login' }">
            <img
              :src="getUrl('images/img/icon/profile.png')"
              alt="phone_icon"
              class="mb-0"
              width="16px"
              height="16px"
            />
            <p class = "mobile_navbar_text" >Account</p>
          </router-link>
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
    cleanContactPhone() {
      // Extract first valid phone number from settings.header_contact_phone
      // Handles cases where multiple phone numbers might be concatenated
      if (!this.settings.header_contact_phone) return '';

      const phone = this.settings.header_contact_phone.toString().trim();

      // If the phone number is unusually long (concatenated numbers), extract the first valid BD number
      // BD phone numbers are typically 11-14 digits (with +880 prefix)
      if (phone.length > 15) {
        // Try to extract a valid BD phone number pattern
        // Look for +880 followed by 10 digits, or 0 followed by 10 digits
        const bdPhonePattern = /(\+?880)?1[3-9]\d{8}/;
        const match = phone.match(bdPhonePattern);

        if (match) {
          return match[0];
        }

        // If no BD pattern found, extract first 11-14 digits
        const digitsOnly = phone.replace(/\D/g, '');
        if (digitsOnly.length >= 11) {
          // Take first 13 digits (typical BD format: 880 + 11 digits)
          return digitsOnly.substring(0, 13);
        }

        // Fallback: return first 11 digits
        return digitsOnly.substring(0, 11);
      }

      return phone;
    },
    cleanFooterPhone() {
      // Extract first valid phone number from settings.footer_contact_phone
      if (!this.settings.footer_contact_phone) return '';

      const phone = this.settings.footer_contact_phone.toString().trim();

      // If the phone number is unusually long (concatenated numbers), extract the first valid BD number
      if (phone.length > 15) {
        const bdPhonePattern = /(\+?880)?1[3-9]\d{8}/;
        const match = phone.match(bdPhonePattern);

        if (match) {
          return match[0];
        }

        const digitsOnly = phone.replace(/\D/g, '');
        if (digitsOnly.length >= 11) {
          return digitsOnly.substring(0, 13);
        }

        return digitsOnly.substring(0, 11);
      }

      return phone;
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
    formatPhoneNumber(phoneNumber) {
      if (!phoneNumber) return '';
      // Remove all non-numeric characters except +
      return phoneNumber.replace(/[^0-9+]/g, '');
    },
  },
};
</script>

<style scoped>
.footer_container {
  background: black;
  color: white;
  padding: 50px 0;
  margin-top: 60px;
}

@media (min-width: 1400px) {
  .footer_container {
    margin-top: 80px;
    padding: 60px 0;
  }
}

@media (max-width: 992px) {
  .footer_container {
    margin-top: 40px;
    padding: 40px 0;
  }
}

@media (max-width: 576px) {
  .footer_container {
    margin-top: 30px;
    padding: 30px 0;
  }
}

.footer-logo-new {
  width: 180px;
  height: 50px;
  margin-left: -11px;
  margin-top: -16px;
}

.social-icon {
  height: 40px;
}

.footer_container p {
  font-size: 14px;
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
  margin-bottom: 0;
}

.footer-menu-new ul li {
  padding: 5px 0;
  font-size: 16px;
}

.footer-menu-new ul li a {
  color:white !important;
}

.footer-small-card {
  background: #ffffff;
  border-radius: 15px;
  padding: 12px 21px;
  align-items: center;
  color: black;
  cursor: pointer;
  text-decoration: none !important;
  transition: all 0.3s ease;
  min-height: 60px;
  display: flex;
}

.footer-small-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-decoration: none !important;
}

.footer-small-card img {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.footer-small-card span {
  margin: 0 15px;
  font-size: 20px;
  color: #ddd;
  flex-shrink: 0;
}

.footer-small-card .details {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
}

.footer-small-card .details span {
  font-size: 13px;
  font-weight: 500;
  color: #212529;
  margin: 0;
  transition: opacity 0.3s ease;
}

.footer-small-card .default-text {
  display: block;
}

.footer-small-card .hover-text {
  display: none;
  font-size: 14px;
  font-weight: 600;
  color: #1BADEB;
}

.footer-small-card:hover .default-text {
  display: none;
}

.footer-small-card:hover .hover-text {
  display: block;
}

.footer-small-card p {
  margin: 4px 0 0 0;
  font-size: 11px;
  color: #666;
  font-weight: 400;
}

/* Tablet */
@media screen and (max-width: 992px) {
  .footer-small-card {
    padding: 10px 18px;
    min-height: 55px;
  }

  .footer-small-card img {
    width: 22px;
    height: 22px;
  }

  .footer-small-card span {
    font-size: 18px;
    margin: 0 12px;
  }

  .footer-small-card .details span {
    font-size: 12px;
  }

  .footer-small-card .hover-text {
    font-size: 13px;
  }

  .footer-small-card p {
    font-size: 10px;
  }
}

/* Mobile Landscape */
@media screen and (max-width: 768px) {
  .footer-small-card {
    padding: 8px 15px;
    min-height: 50px;
    border-radius: 12px;
  }

  .footer-small-card img {
    width: 20px;
    height: 20px;
  }

  .footer-small-card span {
    font-size: 16px;
    margin: 0 10px;
  }

  .footer-small-card .details span {
    font-size: 11px;
  }

  .footer-small-card .hover-text {
    font-size: 12px;
  }

  .footer-small-card p {
    font-size: 9px;
    margin: 3px 0 0 0;
  }

  .footer-small-card.phone-card {
    margin-bottom: 10px;
  }
}

/* Mobile Portrait */
@media screen and (max-width: 576px) {
  .footer-small-card {
    padding: 8px 12px;
    min-height: 48px;
    border-radius: 10px;
  }

  .footer-small-card img {
    width: 18px;
    height: 18px;
  }

  .footer-small-card span {
    font-size: 14px;
    margin: 0 8px;
  }

  .footer-small-card .details {
    flex: 1;
  }

  .footer-small-card .details span {
    font-size: 10px;
    line-height: 1.2;
  }

  .footer-small-card .hover-text {
    font-size: 11px;
  }

  .footer-small-card p {
    font-size: 8px;
    margin: 2px 0 0 0;
  }

  .footer-small-card.phone-card,
  .footer-small-card.store-card {
    width: 100%;
  }
}

/* Small Mobile */
@media screen and (max-width: 390px) {
  .footer-small-card {
    padding: 6px 10px;
    min-height: 45px;
  }

  .footer-small-card img {
    width: 16px;
    height: 16px;
  }

  .footer-small-card span {
    font-size: 12px;
    margin: 0 6px;
  }

  .footer-small-card .details span {
    font-size: 9px;
  }

  .footer-small-card .hover-text {
    font-size: 10px;
  }

  .footer-small-card p {
    font-size: 7px;
  }
}





@media screen and (max-width: 768px) {
  .footer-logo-new {
    height: 58px;
    margin-left: -11px;
    margin-top: -11px;
    margin-right: 17px;
  }
}

@media screen and (max-width: 430px) {
	.footer-logo-new {
    width: 160px;
    height: 45px;
  }


.mobile_navbar_text{
  color: rgb(221, 220, 220);
  font-size: 8px;
}

.mobile_helpline{
  color:  #212529;
}
}
</style>
