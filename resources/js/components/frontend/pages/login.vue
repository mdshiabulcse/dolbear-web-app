<template>
  <div class="sg-page-content">
    <section class="ragister-account text-center">
      <div class="container">
        <div class="account-content">
          <div class="thumb">
            <img loading="lazy" :src="settings.login_banner" alt="login_banner" class="img-fluid">
          </div>
          <div class="form-content" v-if="social_login_active">
            <loading_button :class_name="'social_loading'"></loading_button>
          </div>
          <div class="form-content" v-else>
            <h1>{{ lang.sign_in }}</h1>
            <p>{{ lang.sign_continue_shopping }}</p>
            <form class="ragister-form" name="ragister-form" @submit.prevent="login">
                <div style="">
                  <telePhone @phone_no="getNumber" :phone_error="errors.phone ? errors.phone[0] : null"></telePhone>

                  <span class="validation_error" v-if="errors.phone">{{ errors.phone[0] }}</span>
                </div>

              <div class="form-group mt-4">
                <span class="mdi mdi-name mdi-lock-outline"></span>
                <input type="password" v-model="form.password" class="form-control"
                       :class="{ 'error_border' : errors.password }"
                       :placeholder="lang.Password" required>
              </div>
              <span class="validation_error" v-if="errors.password">{{ errors.password[0] }}</span>

              <div class="middle-content d-flex">
                <div class="form-group remember">
                  <input type="checkbox" name="remember" v-model="form.remember" value="1"
                         id="remember">
                  <label for="remember">{{ lang.remember_me }}</label>
                </div>
                <router-link :to="{name:'reset.password'}">
                  <a href="javaScript:void(0)">{{ lang.forgot_your_password }}</a>
                </router-link>
              </div>

              <loading_button v-if="loading" :class_name="'btn'"></loading_button>
              <button type="submit" v-else class="btn">SIGN IN</button>

              <p>{{ lang.don_have_an_account }}
                <router-link :to="{ name : 'register' }">{{ lang.sign_up }}</router-link>
              </p>
            </form>
          </div>

        </div><!-- /.account-content -->
      </div><!-- /.container -->
    </section><!-- /.ragister-account -->

  </div>
</template>

<script>
import telePhone from "../partials/telephone";
import {getAuth, signInWithPopup, GoogleAuthProvider, FacebookAuthProvider, TwitterAuthProvider} from "firebase/auth";

export default {
  name: "sign_in",
  components: {
    telePhone
  },
  data() {
    return {
      form: {
        phone: '',
        password: '',
        _token: this.token,
        remember: 0,
        captcha: '',
      },
      phoneForm: {
        phone: '',
        otp: '',
      },
      otp_field: false,
      loading: false,
      optionTo: 'phone',
      buttonText: 'Sign In',
      social_login_active: false
    }
  },

  mounted() {
    if (this.authUser) {
      this.$router.go(-1);
    }
    if (this.settings.is_recaptcha_activated == 1) {
      this.captcha();
    }
    this.loginOptions(this.optionTo);
  },
  watch: {
    lang() {
      this.loginOptions(this.optionTo);
    }
  },
  computed: {
    loginRedirect() {
      return this.$store.getters.getLoginRedirection;
    }
  },

  methods: {
    login() {
      let form = this.form;
      let url = this.getUrl('login');

      const axiosWithCredentials = axios.create({
        withCredentials: true
      });
      this.$store.commit('getCountCompare', true);

      this.loading = true;

      axiosWithCredentials.post(url, form).then((response) => {
        this.loading = false;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }

        if (response.data.success) {

          window.captcha = '';
          this.errors = [];

          if (this.loginRedirect) {
            this.$router.push({name: this.loginRedirect});
          } else {
            let user = response.data.user;
            if (user.user_type == 'customer') {
              this.$router.push({name: 'dashboard'});
              /*this.$store.dispatch("activeCurrency", response.data.active_currency);
              this.$store.dispatch("activeLanguage", response.data.active_language);
              this.langKeywords();*/
            } else if (user.user_type == 'admin' || user.user_type == 'staff') {
              this.loading = true;
              document.location.href = this.getUrl('admin/dashboard');
            } else if (user.user_type == 'seller') {
              this.loading = true;
              document.location.href = this.getUrl('seller/dashboard');
            }
          }

          this.$store.dispatch('carts', response.data.carts);
          this.$store.dispatch('user', response.data.user);
          this.$store.dispatch('compareList', response.data.compare_list);
          this.$store.dispatch('wishlists', response.data.wishlists);
        }
      }).catch((error) => {
        this.loading = false;
        if (error.response && error.response.status == 422) {
          this.errors = error.response.data.errors;
        }
      });
    },
    socialLogin(form) {
      this.social_login_active = true;
      let url = this.getUrl('social-login');
      axios.post(url, form).then((response) => {
        this.loading = false;
        this.social_login_active = false;
        if (response.data.success) {
          this.errors = [];
          if (this.loginRedirect) {
            this.$router.push({name: this.loginRedirect});
          } else {
            this.$router.push({name: 'dashboard'});

            this.$store.dispatch('carts', response.data.carts);
            this.$store.dispatch('user', response.data.user);
            this.$store.dispatch('compareList', response.data.compare_list);
            this.$store.dispatch('wishlists', response.data.wishlists);
          }
        } else {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
      }).catch((error) => {
        this.loading = false;
        this.social_login_active = false;
        toastr.error('Something Went Wrong, Please Try Again', this.lang.Error + ' !!');
      })
    },
    loginOptions(optionTo) {
      this.errors = [];
      if (optionTo) {
        if (optionTo == 'phone') {
          if (this.settings.disable_otp)
          {
            this.buttonText = this.lang.sign_in;
          }
          else{
            this.buttonText = this.lang.get_oTP;
          }
          this.optionTo = 'email';
        } else {
          this.buttonText = this.lang.sign_in;
          this.optionTo = 'phone';
        }
      } else {
        this.optionTo = 'email';
        if (this.settings.disable_otp)
        {
          this.buttonText = this.lang.sign_in;
        }
        else{
          this.buttonText = this.lang.get_oTP;
        }

        this.buttonText = this.lang.sign_in;
        this.optionTo = 'phone';
      }

    },
    captcha() {
      const script = document.createElement("script")
      script.src = "https://www.google.com/recaptcha/api.js";
      document.body.appendChild(script);
    },
    copyLoginInfo(email) {
      this.form.email = email;
      this.form.password = '123456';
      this.login('direct_login');
    },
    getNumber(number) {
      this.form.phone = number;
    },
    loginWithSocial(type) {
      let provider = '';
      if (type == 'fb') {
        provider = new FacebookAuthProvider();
        provider.addScope('user_birthday');
        provider.addScope('user_gender');
        provider.addScope('public_profile');
      } else if(type == 'google') {
        provider = new GoogleAuthProvider();
        provider.addScope('profile');
        provider.addScope('email');
      }
      else if(type == 'twitter') {
        provider = new TwitterAuthProvider();
      }

      const auth = getAuth();

      signInWithPopup(auth, provider)
          .then((result) => {
            let raw_user = JSON.parse(result._tokenResponse.rawUserInfo);

            let credential = '';
            let picture = '';

            if (type == 'fb') {
              credential = FacebookAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture.data.url : '';
            } else if(type == 'google') {
              credential = GoogleAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture : '';
            }
            else if(type == 'twitter')
            {
              credential = TwitterAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture : '';
            }

            const token = credential.accessToken;
            // The signed-in user info.
            const user = result.user;

            let form = {
              name: raw_user.name ? raw_user.name : '',
              email: raw_user.email ? raw_user.email : '',
              phone: raw_user.phoneNumber ? raw_user.phoneNumber : '',
              uid: user.uid,
              dob: raw_user.birthday,
              gender: raw_user.gender,
              image: picture,
            };

            this.socialLogin(form);

          }).catch((error) => {
        // Handle Errors here.
        const errorCode = error.code;
        const errorMessage = error.message;
        // The email of the user's account used.
        const email = error.customData.email;
        // The AuthCredential type that was used.
        const credential = GoogleAuthProvider.credentialFromError(error);
        // ...
      });
    },
    langKeywords() {
      let url = this.getUrl('language/keywords');
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.info(response.data.error, this.lang.Info + ' !!');
        } else {
          this.$store.commit('setLangKeywords', response.data.lang);
          let language = response.data.language;
          if (language.text_direction == 'rtl') {
            document.body.setAttribute('dir', 'rtl');
            this.settings.text_direction = 'rtl';
          }
        }
      })
    },
  },
}
</script>
