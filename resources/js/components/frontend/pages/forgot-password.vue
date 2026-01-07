<template>
    <div class="sg-page-content">
        <section class="ragister-account text-center">
            <div class="container">
                <div class="account-content">
                    <div class="thumb">
                        <img :src="settings.forgot_password_banner" alt="forgot_password" class="img-fluid">
                    </div>
                    <div class="form-content">
                        <h1>{{lang.fORGOT_pASSWORD}}</h1>
                        <p v-if="form.otp == null">Enter your phone number to recover your password </p>
                        <p v-if="form.otp != null">{{lang.enter_your_new_password}}</p>
                        <form class="ragister-form" name="ragister-form" @submit.prevent>
                            <div style="margin-bottom: 30px" v-if="!otp">
                              <telePhone :phone="form.phone" @phone_no="getNumber"></telePhone>
                              <span class="validation_error" v-if="errors.phone">{{
                                  errors.phone[0]
                                }}
                              </span>
                            </div>

                          <div class="form-group mt-4" v-if="otp && !resetPass">
                            <span class="mdi mdi-name mdi-lock-outline"></span>
                            <input type="text" v-model="form.otp" class="form-control otp mb-0"
                                   :class="{ 'error_border' : errors.otp }" :placeholder="lang.enter_oTP"/>
                          </div>
                          <div v-if="otp && !resetPass">
                            <p class="count_down_timer">
                              <span v-if="otp && (minute >=0 && second >= 0)">0{{ minute }}:{{ second }}</span>
                              <span @click="submit" v-else>{{ lang.otp_request }}</span>
                            </p>
                          </div>

                          <div class="form-group" v-if="form.otp != null && resetPass">
                                <span class="mdi mdi-name mdi-lock-outline"></span>
                                <input type="password"  v-model="form.newPassword" class="form-control"
                                       :placeholder="lang.new_password" :class="{ 'error_border' : errors.newPassword }">
                            </div>
                            <span class="validation_error" v-if="errors.newPassword">{{ errors.newPassword[0] }}</span>
                            <div class="form-group" v-if="form.otp != null && resetPass">
                                <span class="mdi mdi-name mdi-lock-outline"></span>
                                <input type="password" v-model="form.confirmPassword" class="form-control" :class="{ 'error_border' : errors.confirmPassword }"
                                       :placeholder="lang.confirm_password">
                            </div>
                            <span class="validation_error" v-if="errors.confirmPassword">{{ errors.confirmPassword[0] }}</span>
                            <button  @click="submit" type="submit" v-if="!form.otp && !loading" class="btn" :class="{ 'disable_btn' : this.loading }">{{lang.send}}</button>
                          <loading_button v-if="loading" :class_name="'btn'"></loading_button>
                            <button @click.prevent="verifyOtp" v-if="form.otp && !loading && !resetPass" type="submit" class="btn">Verify OTP</button>
                            <button @click.prevent="createPassword" v-if="form.otp && !loading && resetPass" type="submit" class="btn">{{lang.create_new_password}}</button>
                            <p>Back to <router-link :to="{ name : 'login' }">Sign in</router-link></p>
                        </form>
                    </div>

                </div><!-- /.account-content -->
            </div><!-- /.container -->
        </section><!-- /.ragister-account -->
    </div>
</template>

<script>

import telePhone from "../partials/telephone.vue";

export default {
    name: "forgot_password",
    components: {
      telePhone
    },
    data() {
        return {
          form: {
              phone: "",
              newPassword: "",
              confirmPassword: "",
              otp: ""
          },
          loading : false,

          minute: 1,
          second: 60,
          otp: false,
          resetPass: false,
        }
    },

    methods: {
      countDownTimer() {
        this.minute = 1;
        this.second = 60;
        setInterval(() => {
          this.second--;
          if (this.second == 0) {
            this.minute--;
            this.second = 60;
            if (this.minute == 0) {
              this.minute = 0;
            }
          }
        }, 1000);
      },
      getNumber(number) {
        this.form.phone = number;
      },
      submit(){
            this.loading = true;
            let url = this.getUrl('reset-password')
            axios.post(url,this.form).then((response)=>{
                this.loading = false;
                if (response.data.error)
                {
                    toastr.error(response.data.error, this.lang.Error +' !!' );
                }
                if (response.data.success)
                {
                    this.errors = [];
                    this.otp = true;
                    this.countDownTimer();
                    toastr.success(response.data.success, this.lang.Success +' !!' );
                }
            }).catch((error)=>{
                this.loading = false;
                if (error.response && error.response.status == 422)
                {
                    this.errors = error.response.data.errors;
                }
            })
      },
      verifyOtp(){
        let url = this.getUrl('create-new-password');
        this.loading = true;
        axios.post(url,this.form).then((response)=>{
          this.loading = false;
          if (response.data.error)
          {
            toastr.error(response.data.error, this.lang.Error +' !!' );
          }
          if (response.data.success)
          {
            this.errors = [];
            toastr.success(response.data.success, this.lang.Success +' !!' );
            this.resetPass = true
          }
        }).catch((error)=>{
          this.loading = false;
          if (error.response.status == 422)
          {
            this.errors = error.response.data.errors;
          }
          toastr.error(error.response.data.error, this.lang.Error +' !!' );
        })

        this.loading = false;
      },
      createPassword(){
            let url = this.getUrl('create-new-password');
            this.loading = true;
            axios.post(url,this.form).then((response)=>{
                this.loading = false;
                if (response.data.error)
                {
                    toastr.error(response.data.error, this.lang.Error +' !!' );
                }
                if (response.data.success)
                {
                    this.errors = [];
                    toastr.success(response.data.success, this.lang.Success +' !!' );
                    this.$router.push({name: 'login'});
                }
            }).catch((error)=>{
                this.loading = false;
                if (error.response.status == 422)
                {
                    this.errors = error.response.data.errors;
                }
                toastr.error(error.response.data.error, this.lang.Error +' !!' );
            })

        this.loading = false;
        }

    },
}
</script>
