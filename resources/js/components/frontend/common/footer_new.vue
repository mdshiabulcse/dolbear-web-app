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
        <div class="col-6 col-md-6 col-lg-2 footer-menu-new" >
          <h4>Quick Links</h4>
          <ul v-for="(link, i) in usefulLinks" :key="i">
            <li  >
              <router-link :to="link.url" >{{ link.label }}</router-link>
            </li>
          </ul>
        </div>

        <div class="col-6 col-md-6 col-lg-2 footer-menu-new">
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

          <div class="mt-3">
            <a target="_blank" href="https://www.sslcommerz.com/" title="SSLCommerz">
              <img src="https://securepay.sslcommerz.com/public/image/SSLCommerz-Pay-With-logo-All-Size-01.png" alt="SSLCommerz" class="ssl-logo-img" />
            </a>
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
        height: 60px;
        background-color: black;
        width: 100%;
        z-index: 999;
        padding: 8px 20px 0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
      "
    >

      <div class=" d-flex flex-column align-items-center text-center" style = "margin-top: 6px" >
        <img
          :src="getUrl('images/footer-images/offers.svg')"
          alt="visa_pay_banner"
          class="mb-0"
          width="16px"
            height="16px"
          @click="$router.push('/offers')"
        />
        <p>Offers</p>
      </div>

      <div class=" d-flex flex-column align-items-center text-center" style = "margin-top: 7px" >
        <img
          :src="getUrl('images/footer-images/shop.svg')"
          alt="shop"
          class="mb-0"
          width="16px"
            height="16px"
          @click="$router.push('/shop-all')"
        />
        <p>Shop</p>
      </div>

      <div class=" d-flex flex-column align-items-center text-center">
        <a href="https://wa.me/8801894971070" target="_blank" style="color: white;">
          <svg width="16px" height="16px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white">
            <path d="M20.52 3.48A11.82 11.82 0 0012.04 0C5.4 0 0 5.4 0 12.04c0 2.12.56 4.2 1.64 6.04L0 24l6.12-1.6a11.93 11.93 0 005.92 1.52h.04c6.64 0 12.04-5.4 12.04-12.04 0-3.2-1.24-6.2-3.6-8.4zm-8.48 18.2h-.04a9.88 9.88 0 01-5.04-1.4l-.36-.2-3.64.96.96-3.52-.24-.36a9.9 9.9 0 01-1.56-5.32C2.12 6.6 6.6 2.12 12.04 2.12c2.64 0 5.12 1.04 7 2.92a9.82 9.82 0 012.92 7c0 5.44-4.48 9.92-9.92 9.92zm5.44-7.4c-.28-.12-1.64-.8-1.88-.88-.24-.08-.44-.12-.64.12-.2.28-.72.88-.88 1.04-.16.16-.32.2-.6.08-.28-.12-1.2-.44-2.28-1.4-.84-.72-1.4-1.6-1.56-1.88-.16-.28-.04-.44.08-.56.12-.12.28-.32.4-.48.12-.16.16-.28.24-.48.08-.2.04-.36-.04-.48-.08-.12-.64-1.52-.88-2.08-.24-.6-.48-.52-.64-.52-.16 0-.36 0-.56 0-.2 0-.48.08-.72.36-.24.28-.96.92-.96 2.24s.96 2.6 1.08 2.8c.12.2 1.88 2.88 4.56 4.04.64.28 1.12.44 1.52.56.64.2 1.24.16 1.72.08.52-.08 1.64-.68 1.88-1.32.24-.64.24-1.2.16-1.32-.08-.12-.24-.2-.52-.32z"/>
          </svg>
          <p class = "mobile_navbar_text" >Chat</p>
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
    margin-left: -8px;
    margin-top: -11px;
    margin-right: 17px;
  }

  /* Fix social media icons alignment on mobile */
  .footer-logo-new + div {
    padding: 0 !important;
  }

  .footer-logo-new + div .social-icon {
    height: 35px;
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

/* SSLCOMMERZ Logo Styling */
.ssl-logo-img {
  width: 150px;
  height: auto;
  max-width: 100%;
}

@media screen and (max-width: 768px) {
  .ssl-logo-img {
    width: 120px;
  }
}

@media screen and (max-width: 576px) {
  .ssl-logo-img {
    width: 100px;
  }
}
}
</style>
