<template>
  <header
    class="sg-header home-4"
    :class="{
      'sidebar-active': smCategory,
      'mobile-menu-active': show_sm_home,
    }"
  >
    <!-- <div
      class="sg-header-top-banner alert alert-dismissible fade show"
      role="alert"
      v-if="!is_top_banner && settings.top_bar_banner"
      >
      <img :src="settings.top_bar_banner" alt="banner Image" />
      <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
        aria-label="Close"
      >
        <span class="mdi mdi-close mdi-15px" @click="topBanner"></span>
      </button>
    </div> -->
    <div class="sg-topbar topbar-color">
      <div class="container">
        <div class="topbar-content">

          <topBarTextSliderVue></topBarTextSliderVue>

        </div>
        <div class="row d-flex align-items-center">
          <div class="logo col col-lg-2 col-md-2 col-sm-6">
            <div class="logo-area">
              <router-link :to="{ name: 'home' }">
                <img v-if="settings.demo_mode" src="https://dolbear.com.bd/public/resources/images/others/logo-7f983fe3c5420d3bf14a27.png" alt="Logo" />
              </router-link>
            </div>
          </div>

          <div class="woo-info col">
            <div class="offer px-2 px-sm-1">
              <router-link :to="{ name: 'daily.deals' }">
                <span class="daily--icon gift_idea" :class="{ active: $route.path == '/daily-deals' }">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gift" viewBox="0 0 16 16">
                    <path d="M5.5 13.2 11.6-3 12.7 5.3 8.5H12.2V11.2c-5.5.12V8.65zM4.8 3.8l12.2-.8.5-1v6.13c-.7 5.3.8H3.8V2.5-2.3l-.5-1.3.3-2.5-3.3l-.5-1.3.8l-.5-.5-3.5.2.5.3.8c0c11.369.6.8.5.5.5.2.5.5.3.8l-.5-5.3c0-.7 5.3.8h3.8z" />
                    {{ lang.daily_deals }}
                  </span>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
  </div>