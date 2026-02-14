<template>
  <div>


    <header class="header d-none d-lg-block z-1">
      <div class="container">
        <div class="d-flex justify-content-between">
          <div class="">
            <router-link :to="{ name: 'home' }">
              <img :src="`${baseUrl}/images/img/dolbear_logo.png`" width="150px" alt="logo">
            </router-link>

          </div>
          <div class="header-icon d-flex align-items-center">
            <div class="search-container">
              <img class="search-icon-new" :src="`${baseUrl}/images/img/icon/search.png`" alt="logo">
              <input name="search"
                     class="search-input-new"
                     v-model="phoneSearchKey"
                     type="text"
                     @keyup.enter="handleSearch"
                     autocomplete="off"
                     placeholder="Find your favorite items here" />
              <div v-if="phone_search_products.length > 0" class="searchbox" style="color: white;">
                <ul v-for="product in phone_search_products" :key="product.id">
                  <li>
                    <img :src="product.image_190x230" width="20" height="20" alt="Product Image">
                    <router-link :to="{ name: 'productDetailsNew', params: { slug: product?.slug } }">
                      {{ product?.product_name }}

                    </router-link>
                  </li>
                </ul>
              </div>
            </div>

            <a :href="`tel:${cleanContactPhone}`">
              <img :src="`${baseUrl}/images/img/icon/phone-call.png`" alt="logo">
            </a>

            <a href="https://wa.me/8801894971070" target="_blank" title="Chat on WhatsApp">
              <img :src="`${baseUrl}/images/img/icon/whatsapp.png`" alt="WhatsApp" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block'">
              <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display:none;">
                <path fill="white" d="M20.52 3.48A11.82 11.82 0 0012.04 0C5.4 0 0 5.4 0 12.04c0 2.12.56 4.2 1.64 6.04L0 24l6.12-1.6a11.93 11.93 0 005.92 1.52h.04c6.64 0 12.04-5.4 12.04-12.04 0-3.2-1.24-6.2-3.6-8.4zm-8.48 18.2h-.04a9.88 9.88 0 01-5.04-1.4l-.36-.2-3.64.96.96-3.52-.24-.36a9.9 9.9 0 01-1.56-5.32C2.12 6.6 6.6 2.12 12.04 2.12c2.64 0 5.12 1.04 7 2.92a9.82 9.82 0 012.92 7c0 5.44-4.48 9.92-9.92 9.92zm5.44-7.4c-.28-.12-1.64-.8-1.88-.88-.24-.08-.44-.12-.64.12-.2.28-.72.88-.88 1.04-.16.16-.32.2-.6.08-.28-.12-1.2-.44-2.28-1.4-.84-.72-1.4-1.6-1.56-1.88-.16-.28-.04-.44.08-.56.12-.12.28-.32.4-.48.12-.16.16-.28.24-.48.08-.2.04-.36-.04-.48-.08-.12-.64-1.52-.88-2.08-.24-.6-.48-.52-.64-.52-.16 0-.36 0-.56 0-.2 0-.48.08-.72.36-.24.28-.96.92-.96 2.24s.96 2.6 1.08 2.8c.12.2 1.88 2.88 4.56 4.04.64.28 1.12.44 1.52.56.64.2 1.24.16 1.72.08.52-.08 1.64-.68 1.88-1.32.24-.64.24-1.2.16-1.32-.08-.12-.24-.2-.52-.32z"/>
              </svg>
            </a>

            <router-link :to="{ name: 'offers' }">
              <img :src="`${baseUrl}/images/img/icon/gift.png`" alt="logo">
            </router-link>
            <router-link :to="{ name: 'TrackOrderNew' }">
              <img :src="`${baseUrl}/images/img/icon/fast-delivery.png`" alt="logo">
            </router-link>
            <router-link v-if="authUser && authUser?.user_type === 'customer'" :to="{ name: 'dashboard' }">
              <img :src="`${baseUrl}/images/img/icon/profile.png`" alt="Profile">
            </router-link>
            <router-link v-else :to="{ name: 'login' }">
              <img :src="`${baseUrl}/images/img/icon/profile.png`" alt="Profile">
            </router-link>

          </div>


        </div>
      </div>
    </header>

    <!-- cart -->
    <div class=" offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" >
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">SHOPPING CART</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">X <span class="ms-1"
            style="font-size: 15px;">CLOSE</span></button>
      </div>
      <div class="offcanvas-body">

        <div class="cart-item" v-for="(item, index) in carts" :key="index">
          <div class="cart-item-img">
            <img :src="item.image_72x72" alt="cart">
          </div>
          <div class="cart-item-details">
            <p class="item-name mb-1">{{ item.product_name }} <span v-if="item.variant">({{ item.variant }})</span></p>
            <!-- input -->
            <div class="product-quantity">
              <div class="quantity" data-trigger="spinner">
                <a class="btn pull-left" @click="cartMinus(item)" href="javascript:void(0);" data-spin="down"><span
                    class="mdi mdi-name mdi-minus"></span></a>
                <input type="text" name="quantity" v-model="item.quantity" title="quantity" readonly class="input-text">
                <a class="btn pull-right" @click="cartPlus(item.id)" href="javascript:void(0);" data-spin="up"><span
                    class="mdi mdi-name mdi-plus"></span></a>
              </div>
            </div>
            <div class="cart-item-details-quantity mt-2">
              <p class="mb-0"><span class="item-qnt"> ৳ {{ item.quantity * (item.price - item.discount) }} <span
                    class="item-qnt-details">( ৳ {{ item.quantity }} x {{ (item.price - item.discount) }} )</span></span> </p>
            </div>

          </div>
          <img src="/images/img/icon/deletecart.png" @click="deleteCart(item.id)" alt="" srcset=""
            style="cursor: pointer;">
        </div>

      </div>
      <div class="offcanvas-footer">

        <p>Sub Total: {{ subTotal }}৳</p>
        <span></span>
        <p>Total: {{ total }}৳</p>

        <!-- <button class="view-cart col-6" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
          aria-controls="offcanvasRight">
          <router-link :to="{ name: 'cart' }" class="view-cart-link">
            View Cart
          </router-link>
        </button> -->
        <button class="checkout-btn" @click="checkout()" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
          aria-controls="offcanvasRight">
          Checkout
        </button>



      </div>
    </div>
    <!-- navbar -->

    <!-- <div class="search-result" v-if="search_products.length > 0">
                <ul class="product-ul z-2">
                    <li v-for="product in search_products" :key="product.id">
                        <div class="product-item">
                            <a :href="'product/' + product.slug" class="z-1">
                              <div class="product-details">
                                <img :src="product.image_190x230" class="z-1" alt="Product Image">
                                <div class="z-1">
                                    <h3>{{ product.product_name }}</h3>
                                </div>
                            </div>
                            </a>
                        </div>
                    </li>
                </ul>
              </div> -->

    <div ref="navbar" class="navbar mobile-navbar" :style="navbarStyles">
      <div class="container">
        <div class="row v-center ms-auto ms-md-0 w-100">
          <!-- menu start here -->
          <div class="header-item item-center">
            <div class="menu-overlay" :class="{ 'active': is_menu_active }" @click.stop="closeMenu"></div>
            <nav class="menu" :class="{ 'active': is_menu_active }">
              <div class="mobile-menu-head" :class="{ 'active': is_sub_menu_active }">
                <div class="go-back" @click="is_sub_menu_active = false; menu_key = null">
                  <svg height="48" stroke="white" viewBox="0 0 9 48" width="9" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="m1.5618 24.0621 6.5581-6.4238c.2368-.2319.2407-.6118.0088-.8486-.2324-.2373-.6123-.2407-.8486-.0088l-7 6.8569c-.1157.1138-.1807.2695-.1802.4316.001.1621.0674.3174.1846.4297l7 6.7241c.1162.1118.2661.1675.4155.1675.1577 0 .3149-.062.4326-.1846.2295-.2388.2222-.6187-.0171-.8481z">
                    </path>
                  </svg>
                </div>
                <div class="current-menu-title"></div>
                <div class="mobile-menu-close text-light" @click="closeMenu">&times;</div>
              </div>
              <ul class="menu-main p-0 mb-0 d-lg-flex flex-lg-row justify-content-lg-between d-md-flex flex-md-column">
                <li class="nav-item menu-item-has-children" v-for="(menu, i) in headerMenu" :key=i>
                  <router-link @click.native="handleMenuClick(i, menu)"
                    :to="menu.url" class="nav-items">
                    {{ menu.label }}
                  </router-link>


                  <div v-if="Object.keys(menu).length > 2" class="sub-menu mega-menu mega-menu-column-4"
                    :class="{ 'active': menu_key === i }">
                    <div class="container row">
                      <div class="list-item col-md-3">
                        <ul>
                          <li v-for="(value, key, j) in menu" v-if="key !== 'label' && key !== 'url'" :key="j">
                            <router-link @click.native="closeMenu" :to="value.url"> {{ value.label
                              }}</router-link>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                </li>

              </ul>
            </nav>
          </div>
          <!-- menu end here -->
          <div class="header-item item-right d-flex align-items-center justify-content-between d-lg-none">
            <div class="left">
              <router-link :to="{ name: 'home' }">
                <img width="80px" :src="`${baseUrl}/images/img/dolbear_logo.png`" alt="logo">
              </router-link>
            </div>
            <!-- mobile menu trigger -->
            <div class="right d-flex align-items-center">
              <div class="d-flex mobile-serach-cart align-items-center me-1">
                <router-link @click.native="is_search_box_active = true" :to="''" class="mobile-serach-icon">
                  <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M15.3716 14.5L12.6494 11.7778M12.2605 5.94444C12.2605 6.65942 12.1196 7.36739 11.846 8.02794C11.5724 8.68849 11.1714 9.28868 10.6658 9.79425C10.1603 10.2998 9.56008 10.7008 8.89953 10.9745C8.23898 11.2481 7.531 11.3889 6.81603 11.3889C6.10105 11.3889 5.39308 11.2481 4.73253 10.9745C4.07198 10.7008 3.47179 10.2998 2.96622 9.79425C2.46066 9.28868 2.05962 8.68849 1.78602 8.02794C1.51241 7.36739 1.37158 6.65942 1.37158 5.94444C1.37158 4.50049 1.94519 3.11567 2.96622 2.09464C3.98725 1.07361 5.37207 0.5 6.81603 0.5C8.25998 0.5 9.6448 1.07361 10.6658 2.09464C11.6869 3.11567 12.2605 4.50049 12.2605 5.94444Z"
                      stroke="white" stroke-linecap="round" />
                  </svg>
                </router-link>
                <!-- <a href="#" class="mobile-cart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5.3916 7.08824V3.79412C5.3916 2.92046 5.73866 2.08259 6.35643 1.46482C6.97419 0.847058 7.81207 0.5 8.68572 0.5C9.55937 0.5 10.3972 0.847058 11.015 1.46482C11.6328 2.08259 11.9798 2.92046 11.9798 3.79412V7.08824" stroke="white" stroke-linecap="round"/>
                      <path d="M1.84549 7.63838C1.96491 6.20462 2.02502 5.48815 2.49773 5.0525C2.97044 4.61768 3.6902 4.61768 5.12891 4.61768H12.2434C13.6813 4.61768 14.401 4.61768 14.8737 5.0525C15.3464 5.48732 15.4066 6.20462 15.526 7.63838L15.9493 12.7163C16.0184 13.5505 16.053 13.968 15.8093 14.234C15.5638 14.5 15.1455 14.5 14.3071 14.5H3.06432C2.22679 14.5 1.80761 14.5 1.56302 14.234C1.31844 13.968 1.35302 13.5505 1.42302 12.7163L1.84549 7.63838Z" stroke="white"/>
                      </svg>                    
                  </a> -->
              </div>
              <div class="mobile-menu-trigger" @click="activeToggleMenuMobile">
                <!-- <span></span> -->
                <svg width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M0 0.5H15" stroke="white" />
                  <path d="M0 8.5H15" stroke="white" />
                </svg>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- mobile searchbar -->
    <div class="mobile-search" :class="is_search_box_active ? 'show' : ''">
      <div class="close-search" @click="is_search_box_active = false">
        X
      </div>
      <div class="mt-5 d-flex justify-content-center position-relative">
        <input
            type="search"
            v-model="phoneSearchKey"
            @keyup.enter="handleSearch"
            placeholder="Search"
            class="form-control search-input"
          style="padding-left: 40px;" />
        <!-- <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
          <path
            d="M21.71 20.29l-4.78-4.78A7.92 7.92 0 0 0 18 10c0-4.41-3.59-8-8-8S2 5.59 2 10s3.59 8 8 8c1.92 0 3.68-.68 5.06-1.81l4.78 4.78c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41zM10 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"
            stroke="white" />
        </svg> -->
      </div>

      <div class="mobile-search-quicke-link">

        <div v-for="product in phone_search_products" :key="product.id">
          <a @click="closeMobileSearchAndNavigate" :href="'/product/' + product.slug">{{ product.product_name }}</a>
        </div>

      </div>
    </div>

    <!-- cart -->
    <button class="cart-button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
      aria-controls="offcanvasRight">
      <!-- Your cart icon here -->

      <span v-if="carts?.length" class="position-absolute translate-middle badge rounded-pill bg-dark"
        style="font-size: 16px; padding: 2px 6px; font-weight: 400; top: 3px; right: -7px">
        {{ carts?.length }}
      </span>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag"
        viewBox="0 0 16 16">
        <path
          d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
      </svg>
    </button>

    <direct_buy_modal ref="directBuyModal" />

  </div>


</template>

<script>
import About from "../pages/about";
import detailsView from "./details-view";
import shimmer from "../partials/shimmer";
import sidebar_categories from "../partials/sidebar_categories";
import topBarTextSliderVue from "../homepage/top_bar_text_slider.vue";
import newNavBar from "../homepage/new_nav_bar.vue";
import direct_buy_modal from "../common/direct_buy_modal.vue";

export default {
  name: "headerNew",
  components: {direct_buy_modal, About, detailsView, shimmer, sidebar_categories, topBarTextSliderVue, newNavBar },
  data() {
    return {
      isSticky: false,
      navbarTop: 0,

      mobile_child_id: 0,
      mobile_children_id: 0,
      language_dropdown: false,
      currency_dropdown: false,
      // searchKey: this.$route.query.q,
      phone_search_products: [],
      search_products: [],
      searchKey: "",
      phoneSearchKey: "",
      menu: false,
      subMenu: false,
      search_bar: false,
      show_search_icon: false,
      show_sm_home: false,
      search_key_focus: false,
      active: false,
      home_child_id: 0,
      is_top_banner: !!localStorage.getItem("top-banner"),

      messages: ['Message 1', 'Message 2', 'Message 3'],
      currentMessage: '',
      messageIndex: 0,

      is_menu_active: false,
      is_sub_menu_active: false,
      menu_key: null,

      is_search_box_active: false,

      baseUrl: "",


    };
  },
  mounted() {
    document.addEventListener("click", this.handleOutsideClick);

    window.addEventListener('scroll', this.handleScroll);
    this.navbarTop = this.$refs.navbar.offsetTop;

    if (!this.lang) {
      this.$store.dispatch("languageKeywords");
    }

    this.getFlashMessages()

    setInterval(() => {
      this.messageIndex = (this.messageIndex + 1) % this.messages.length;
      this.currentMessage = this.messages[this.messageIndex].message;
    }, 3000);

		this.baseUrl = `${window.location.protocol}//${window.location.host}`;

  },
  beforeUnmount() {
    document.removeEventListener("click", this.handleOutsideClick);
  },


  beforeDestroy() {
    window.removeEventListener('scroll', this.handleScroll);
  },

  watch: {
    searchKey: function (val) {
      if (val) {
        this.searchProducts();
      } else {
        this.search_products = [];
      }
    },
    phoneSearchKey: function (val) {
      if (val) {
        this.phoneSearchProducts();
      } else {
        this.phone_search_products = [];
      }
    },
    $route() {
      if (this.is_menu_active) {
        this.closeMenu();
      }
    }
  },
  computed: {
    navbarStyles() {
      return {
        position: this.isSticky ? 'fixed' : 'relative',
        top: this.isSticky ? '0' : 'auto',
        zIndex: this.isSticky ? '1000' : '5555',
        width: '100%',
        padding: '15px',

        backgroundColor: this.isSticky ? 'rgba(0, 0, 0, 0.8)' : 'rgba(0, 0, 0, 0.8)',
        color: this.isSticky ? 'white' : 'black',
        // backgroundColor: 'black !important',
        // color: 'white',
        height: '55px',
        display: 'flex',

        transition: 'top 0.5s cubic-bezier(0.4, 0, 0.6, 1)',
        opacity: '0.95',
  
        

      };
    },
    languages() {
      return this.$store.getters.getLanguages;
    },
    currencies() {
      return this.$store.getters.getCurrencies;
    },
    activeLanguage() {
      return this.$store.getters.getActiveLanguage;
    },
    activeCurrency() {
      return this.$store.getters.getActiveCurrency;
    },
    carts() {
      return this.$store.getters.getCarts;
    },
    subTotal() {
      return this.carts?.reduce((total, item) => total + item.quantity * (item.price - item.discount), 0);
    },
    total() {
      return this.subTotal;
    },
    visibleCategory() {
      let categories = this.$store.getters.getCategories;
      return categories.length > 0 ? categories : [];
    },
    headerMenu() {
      return this.settings.header_menu;
    },
    wishlists() {
      return this.$store.getters.getTotalWishlists;
    },
    active_modal() {
      return this.$store.getters.getActiveModal;
    },
    productDetails() {
      let products = this.$store.getters.getProductDetails;
      for (let i = 0; i < products.length; i++) {
        if (products[i].slug == this.active_modal) {
          return products[i].product;
        }
      }
      return false;
    },
    navbar_class() {
      return this.$store.getters.getNavBarClass;
    },
    smCategory() {
      return this.$store.getters.getSmCategory;
    },

    compareList() {
      return this.$store.getters.getCompareList;
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
  },
  methods: {

    handleOutsideClick(event) {
      const offcanvas = document.querySelector(".offcanvas.show"); // Active Offcanvas
      const backdrop = document.querySelector(".offcanvas-backdrop"); // Backdrop element

      if (offcanvas && backdrop) {
        const isClickInsideOffcanvas = offcanvas.contains(event.target);

        // Remove the backdrop if the click is outside the Offcanvas
        if (!isClickInsideOffcanvas) {
          backdrop.remove(); // Completely removes the backdrop element
          document.body.classList.remove("offcanvas-open"); // Cleanup body class
        }
      }
    },
        
    handleSearch() {

      const searchKey = this.phoneSearchKey
      this.phoneSearchKey = ''
      this.phone_search_products = []
      this.is_search_box_active = false

      if (searchKey.trim()) {
        const newQuery = { q: searchKey.trim() };

        // Check if the current route is already the target route with the same query
        if (
            this.$route.name !== "all.products.new" ||
            this.$route.query.q !== newQuery.q
        ) {
          this.$router.push({
            name: "all.products.new",
            query: newQuery,
          });
        }
      }
    },

    activeToggleMenuMobile() {
      this.is_menu_active = !this.is_menu_active;
    },

    closeMenu() {
      this.is_menu_active = false;
      this.is_sub_menu_active = false;
      this.menu_key = null;
    },

    handleMenuClick(i, menu) {
      const hasChildren = Object.keys(menu).length > 2;
      if (hasChildren) {
        this.subMenuActive(i, menu);
      } else {
        setTimeout(() => {
          this.closeMenu();
        }, 100);
      }
    },

    subMenuActive(i, menu) {
      this.menu_key = i;
      this.is_sub_menu_active = true;
    },

    handleScroll() {
      this.isSticky = window.pageYOffset > this.navbarTop;
    },

    async getFlashMessages() {
      let url = this.getUrl('flash-message/all');
      this.$Progress.start();

      axios.get(url).then((response) => {
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          this.messages = response.data.data

          if (this.messages.length > 0) {
            this.currentMessage = this.messages[0].message;
          }

          this.$Progress.finish();
        }
      }).catch((error) => {
        this.$Progress.fail();
      })
    },
    cartPlus(id) {

      let formData = {
        id: id,
        quantity: 1,
      };

      let url = this.getUrl('cart/update');
      axios.post(url, formData).then((response) => {

        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$store.dispatch('carts', response.data.carts);
          let coupons = response.data.coupons;
          this.parseData(this.cartList, response.data.checkouts, coupons);
        }
      })

    },

    cartMinus(item) {

      if (item.quantity > 1) {
        let formData = {
          id: item.id,
          quantity: -1,
          status: 'minus',
        };

        let url = this.getUrl('cart/update');

        axios.post(url, formData).then((response) => {

          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.$store.dispatch('carts', response.data.carts);
            let coupons = response.data.coupons;
            let checkouts = response.data.checkouts;
            this.parseData(this.cartList, checkouts, coupons);
          }
        })
      } else {
        this.deleteCart(item.id)
      }



    },

    deleteCart(id) {
      if (confirm("Are you sure?")) {
        let url = this.getUrl('cart/delete/' + id);
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.$store.dispatch('carts', response.data.carts);
          }
        })
      }
    },

    checkout() {
      if (!this.authUser) {
        this.$refs.directBuyModal.showOrderModal()
      }
      if (this.authUser.user_type !== 'customer') {
        return toastr.warning(this.lang.you_are_not_able_topurchase_products, this.lang.Warning + ' !!');
      }
      this.$router.push({ name: 'cartNew' });
    },

    subMenuToggle(event) {
      if (screen.width > 991) {
        if (event.type != "click") {
          this.subMenu = true;
        }
      } else {
        if (event.type == "click") {
          this.subMenu = !this.subMenu;
        }
      }
    },
    toggleNavClass() {
      return {
        "fixed-top": this.navbar_class,
        "sticky-bg": this.addons.includes("ishopet"),
        "ishopet-header": this.addons.includes("ishopet"),
      };
    },
    changeLanguage(locale) {
      let url = this.getUrl("change/locale/" + locale);
      this.language_dropdown = false;
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.info(response.data.error, this.lang.Info + " !!");
        } else {
          window.location.reload();
        }
      });
    },
    changeCurrency(currency) {
      let url = this.getUrl("change/currency/" + currency.code);
      this.currency_dropdown = false;
      this.$Progress.start();
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.info(response.data.error, this.lang.Info + " !!");
        } else {
          this.$store.dispatch("activeCurrency", response.data.active_currency);
          this.$Progress.finish();
        }
      });
    },
    currencyDropdown() {
      this.currency_dropdown = !this.currency_dropdown;
      this.currency_dropdown &&
        this.$nextTick(() => {
          document.addEventListener("click", this.hideCurrencyDropdown);
        });
    },
    hideCurrencyDropdown: function () {
      this.currency_dropdown = false;
      document.removeEventListener("click", this.hideCurrencyDropdown);
    },
    languageDropdown() {
      this.language_dropdown = !this.language_dropdown;
      this.language_dropdown &&
        this.$nextTick(() => {
          document.addEventListener("click", this.hideLanguageDropdown);
        });
    },
    hideLanguageDropdown: function () {
      this.language_dropdown = false;
      document.removeEventListener("click", this.hideLanguageDropdown);
    },
    searchDropdown() {
      this.search_key_focus = true;
      this.search_key_focus &&
        this.$nextTick(() => {
          document.addEventListener("click", this.hideSearchDropdown);
        });
    },
    hideSearchDropdown: function () {
      this.search_key_focus = false;
      document.removeEventListener("click", this.hideSearchDropdown);
    },
    deleteCart(id) {
      if (confirm("Are you sure?")) {
        let url = this.getUrl("cart/delete/" + id);
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            this.$store.dispatch("carts", response.data.carts);
          }
        });
      }
    },
    searchProducts() {
      this.search_bar = true;
      let url = this.getUrl("search/product");
      let form = { key: this.searchKey };
      axios
        .post(url, form)
        .then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            this.search_products = response.data.products;

          }
        })
        .catch((error) => {
          this.search_products = [];
        });
    },

    phoneSearchProducts() {
      this.search_bar = true;
      let url = this.getUrl("search/product");
      let form = { key: this.phoneSearchKey };
      axios
        .post(url, form)
        .then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + " !!");
          } else {
            this.phone_search_products = response.data.products;

          }
        })
        .catch((error) => {
          this.phone_search_products = [];
        });
    },

    closeMobileSearchAndNavigate() {
      // Close the mobile search overlay when a result is clicked
      this.is_search_box_active = false;
      // Clear the search input and results
      this.phoneSearchKey = '';
      this.phone_search_products = [];
    },

    categoryMenu() {
      this.$store.commit("setSmCategory", !this.smCategory);
      this.show_sm_category = !this.show_sm_category;
      this.show_sm_home = false;
      this.show_sm_category &&
        this.$nextTick(() => {
          document.addEventListener("click", this.hideCategoryMenu);
        });
    },
    homeMenu() {
      // this.$store.commit('setSmCategory',false)
      this.show_sm_home = !this.show_sm_home;
      this.show_sm_home &&
        this.$nextTick(() => {
          document.addEventListener("click", this.hideHomeMenu);
        });
    },
    hideCategoryMenu: function () {
      this.$store.commit("setSmCategory", false);
      this.show_sm_category = false;
      document.removeEventListener("click", this.hideCategoryMenu);
    },
    hideHomeMenu: function () {
      this.show_sm_home = false;
      document.removeEventListener("click", this.hideHomeMenu);
    },
    toggleMobileMenu(id) {
      if (this.mobile_child_id == id) {
        this.mobile_child_id = 0;
      } else {
        this.mobile_child_id = id;
      }
      return this.show_mobile_child == "d-none"
        ? (this.show_mobile_child = "d-block")
        : (this.show_mobile_child = "d-none");
    },
    topBanner() {
      localStorage.setItem("top-banner", "1");
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
    checkoutPage(event) {
      event.preventDefault();
      if (!this.authUser && this.settings.disable_guest) {
        toastr.error(this.lang.login_first, this.lang.Error + " !!");
        this.$store.commit("setLoginRedirection", this.$route.name);
        if (this.$route.name != "login") {
          return this.$router.push({ name: "login" });
        }
        return false;
      }

      if (this.$route.name != "checkout") {
        return this.$router.push({ name: "checkout", query: { cart_page: 1 } });
      }
      return true;
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
/* new header css */

.header-icon img {
  margin: 0px 18px;
}



/* new header css */




.sticky {
  position: fixed;
  top: 0;
  z-index: 1000;
}



button.input-group-text.search-input-btn {
  height: 40px !important;
}

.extra-padding {
  padding-top: 15px;
  padding-bottom: 15px;
}

.topbar-color {
  background-color: #57D9FF;
}

.navbar-color {
  background-color: #0B0B0B !important;
}

.search-input-color {
  background-color: #0B0B0B;
}

.sg-menu .navbar li a {
  color: #fff !important;
}

.nav-link-color {
  color: #0B0B0B !important;
}

.btn {
  background-color: #fff;
}

input.input-text {
  width: 50px;
}

.view-cart-link {
  color: #fff;
}


/* cart */
.cart-container {
  position: fixed;
  bottom: 20px;
  /* Adjust the distance from the bottom as needed */
  right: 20px;
  /* Adjust the distance from the right as needed */
  z-index: 999;
  /* Adjust the z-index as needed to ensure it's above other content */
}

.cart-container i {
  font-size: 24px;
  /* Adjust the icon size as needed */
  color: #333;
  /* Adjust the icon color as needed */
  cursor: pointer;
  transition: transform 0.3s ease;
  /* Add a transition effect */
}

.cart-container i:hover {
  transform: scale(1.2);
  /* Scale up the icon on hover */
  color: #ff0000;
  /* Change the color on hover if needed */
}

.cart-button {
  position: fixed;
  bottom: 120px;
  right: 100px;
  background-color: #57D9FF;
  /* Background color for the circle */
  width: 60px;
  /* Diameter of the circle */
  height: 60px;
  border-radius: 50%;
  /* Make it round */
  border: none;
  /* Remove border */
  cursor: pointer;
  z-index: 999;
}

/* Medium screens (tablets, 768px and up) */
@media (max-width: 1024px) {
  .cart-button {
    right: 100px;
  }
}

/* Small screens (phones, 600px and up) */
@media (max-width: 768px) {
  .cart-button {
    right: 30px;
  }
}

/* Extra small screens (phones, 600px and down) */
@media (max-width: 600px) {
  .cart-button {
    right: 10px;
  }
}

.cart-button svg {
  width: 24px;
  /* Adjust the size of the icon as needed */
  height: 24px;
  fill: black;
  /* Icon color */
}

/* dropdown menu */
.search-result {
  overflow-y: auto;
  max-height: 500px;
  max-width: 418px;
  position: absolute;
  background-color: white;
  padding: 10px;
}

/* Product item container */
.product-item {
  display: flex;
  flex-direction: column;
}

/* Anchor tag styles */
.product-item a {
  text-decoration: none;
  color: inherit;
  /* Inherit the color from parent */
}

/* Product image */
.product-details img {
  width: 20%;
  height: auto;
  margin-bottom: 10px;
}

/* Product details */
.product-details {
  padding: 2px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
}

/* Product name */
.product-details h3 {
  margin-top: 0;
}

/* Product description */
.product-details p {
  margin-bottom: 10px;
}

/* Add to cart button */
.product-details button {
  padding: 5px 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
}

.product-details button:hover {
  background-color: #0056b3;
}

.product-ul {
  padding-left: 0px !important;
}

.product-details {
  display: flex;
  align-items: center;
}

.product-details img {
  margin-right: 10px;
}



/* offcanvas new design */
.offcanvas {
  background: white !important;
  height: 100% !important;
  overflow-y: scroll !important;
}

.offcanvas-body {
  background-color: white !important;
  height: calc(93vh - 145px) !important;
}

.offcanvas-header {
  background: black;
}

.cart-item {
  align-items: center !important;
  border-bottom: none !important;
  display: flex;
  justify-content: space-between;
}

.item-qnt {
  color: #0B0B0B !important;
  font-size: 15px !important;
}

span.item-qnt-details {
  font-size: 12px !important;
  color: black !important;
  font-weight: 500;
}

.offcanvas-title {
  font-size: 15px !important;
  font-weight: 600 !important;
}

.offcanvas .offcanvas-header button {
  font-size: 15px !important;
  font-weight: 600 !important;
  margin-top: 0;
}


.offcanvas-body .item-name {
  color: #0B0B0B !important;
  font-size: 14px !important;
  font-weight: 500 !important;
}

.offcanvas .offcanvas-footer {
  border-top: none !important;
  background: white !important;
  height: 145px;

}

.checkout-btn {
  background: #168FC3;
  color: white !important;
  border-radius: 5px !important;
  width: 100% !important;
  padding: 10px !important;
  border: none !important;
  margin: 15px 0 !important;

}

.offcanvas-footer p {
  color: #0B0B0B !important;
  font-size: 14px !important;
  font-weight: 500 !important;
  margin-bottom: 0;

}

.offcanvas-footer span {
  width: 100% !important;
  height: 1px !important;
  background: #014F71 !important;
  display: block !important;
  margin: 15px 0 !important;
}

.search-input {
  width: 85%;
  height: 40px;
}

.mobile-search-quicke-link {
  margin-top: 0;
}

.search-container {
  position: relative;
  display: inline-flex;
  align-items: center;
  cursor: pointer;
}

input.search-input-new {
  position: relative;
  width: 0px;
  transition: width 1.3s ease, opacity 0.3s ease;
  padding: 0px;
  opacity: 0;
  pointer-events: none;
  border: none;
  border-radius: 5px;
}

.search-container:hover input.search-input-new {
  padding: 5px 0px 5px 15px;
  /* width: 410px; */
  width: 831px;
  opacity: 1;
  pointer-events: auto;
}

.searchbox {
  position: absolute;
  width: 0px;
  transition: width 1.3s ease, opacity 0.3s ease;
  opacity: 0;
  background: black;
  border: 2px solid white;
  border-radius: 8px;
  height: auto;
  max-height: 462px;
  overflow-y: auto;
  overflow-x: hidden;
  top: 35px;
  padding-top: 21px;
  margin-left: 63px;
  z-index: 9999 !important;
}

.search-container:hover .searchbox {
  /* width: 410px; */
  width: 831px;
  opacity: 1;
  pointer-events: auto;


}

.searchbox a {
  color: white !important;
}

.searchbox ul li:hover {
  background: #57D9FF;
  padding: 0px 14px;
  width: fit-content;
  cursor: pointer;
  border-radius: 5px;
}




@media screen and (max-width: 991px) {
  .mobile-navbar {
    background-color: black !important;
  }

  .menu-item-has-children a {
    color: white !important;
    font-size: 25px !important;
    font-weight: 500 !important;
  }

  .mobile-search-quicke-link a {
    font-weight: 500;
    line-height: 35px;
    cursor: pointer !important;
  }

  .mobile-search-quicke-link a:hover {
    color: red !important;
  }

}

@media screen and (max-width: 768px) {
  .offcanvas-body {
  background-color: white !important;
  height: auto !important;
}

/* Mobile search suggestions dropdown style */
.mobile-search-quicke-link {
  position: relative;
  width: 100%;
  max-width: 100%;
  max-height: 300px;
  overflow-y: auto;
  background: rgba(0, 0, 0, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  padding: 10px 0;
  margin-top: 15px;
  z-index: 10;
}

.mobile-search-quicke-link div {
  margin-bottom: 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-search-quicke-link div:last-child {
  border-bottom: none;
}

.mobile-search-quicke-link a {
  display: flex;
  align-items: center;
  color: white !important;
  text-decoration: none;
  padding: 12px 15px;
  transition: background-color 0.2s ease;
  font-size: 14px;
  line-height: 1.4;
}

.mobile-search-quicke-link a:hover {
  background-color: #57D9FF;
  color: black !important;
}
}
</style>