<template>
    <div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
            ref="modal">
            <div class="modal-dialog custom-modal-css">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Product Details</h5>
                        <button type="button" class="btn-close" @click="hideModal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <Product_details_card :productDetails="product" v-if="product" />
                        <Shimmer height="450" v-else/>
                    </div>
                 
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import Product_details_card from './product_details_card.vue';
import Shimmer from '../partials/shimmer.vue';

export default {
    components: {
        Product_details_card,
        Shimmer
    },
    props: {
        product: {
            type: Object,
            required: true,
        },
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
        showModal() {
            if (!this.modalInstance) {
                this.modalInstance = new Modal(this.$refs.modal);
            }
            this.modalInstance.show();
        },
        hideModal() {
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
</style>

