<template>
    <div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
            ref="modal">
            <div class="modal-dialog custom-modal-css">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Order</h5>
                        <button type="button" class="btn-close" @click="hideOrderModal"></button>
                    </div>
                    <div class="modal-body direct-buy-modal">
                        <h2>Are you ready to finalize your order?</h2>
                        <p>if you don’t login your account yet you can do that from here or select direct buy to
                            purchase without login.</p>

                        <div>
                            <button @click="login" class="login">Login</button>
                            <button @click="goToDirectBuy" class="direct-buy">Direct Buy</button>
                            <!-- <button class="login" @click="hideOrderModal">Close</button> -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import Shimmer from '../partials/shimmer.vue';

export default {
    components: {
        Shimmer
    },
    props: {
        isloading: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            modalInstance: null,
        };
    },
    methods: {
      login(){
        this.hideOrderModal()
        this.$store.commit('setLoginRedirection', 'cartNew');
        this.$router.push({name: 'login'});
        return false;
      },
      goToDirectBuy() {
        this.hideOrderModal()
        this.$router.push({ name: 'guestCheckout' }); // Replace 'DirectBuyPage' with your route name
      },
      showOrderModal() {
          if (!this.modalInstance) {
              this.modalInstance = new Modal(this.$refs.modal);
          }
          this.modalInstance.show();
      },
      hideOrderModal() {
          if (this.modalInstance) {
              this.modalInstance.hide();
          }
      },
    },


};
</script>

<style>
.custom-modal-css {
    max-width: 1121px;
    top: 15%;
}

.direct-buy-modal {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 30vh;
}

.direct-buy-modal button {
    width: 142px;
    height: 30px;
    border-radius: 5px;
    border: none;
}

.direct-buy-modal button.login {
    background: #F5F5F7;
    margin-right: 3px;
}
.direct-buy-modal button.direct-buy {
    background: #1E6AAF;
    color: #fff;
}
</style>
