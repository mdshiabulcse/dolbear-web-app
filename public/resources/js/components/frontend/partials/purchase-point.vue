<template>
  <div class="accordion" id="purchaseAccordion">
    <div class="accordion-item">
      <div class="accordion-header" id="headingOne">
        <button
          class="accordion-button"
          type="button"
          data-bs-toggle="collapse"
          aria-expanded="true"
          aria-controls="collapseOne"
         @click="purchase_collapse = !purchase_collapse" :class="{ collapsed: !purchase_collapse }"
        >
          <span>
            <img
              :src="getUrl('images/others/pencil1.png')"
              alt="Image"
              class="img-fluid" /></span
          >Use Purchase Point
        </button>
      </div>
      <div
        id="purchaseCollapse"
        class="accordion-collapse collapse"
        aria-labelledby="headingOne"
        data-bs-parent="#purchaseAccordion"
        :class="{ show: purchase_collapse }"
      >
        <div class="accordion-body">
          <div class="coupon-code-list">
            <div>
              <p>
                Available Point: <span>{{ purchase_point }}</span>
              </p>
            </div>
          </div>
          <form @submit.prevent="applyPurchasePoint">
            <input
              type="text"
              v-model="payment_form.use_purchase_point"
              class="form-control"
              placeholder="Enter Purchase Point"
              required
            />
            <loading_button
              v-if="loading"
              :class_name="'opacity_5'"
            ></loading_button>
            <button v-else>{{ lang.apply }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
  
  <script>
export default {
  name: "purchase-point",
  props: ["purchase_point", "cartList", "trx_id"],
  data() {
    return {
      loading: false,
      purchase_collapse: false,
    };
  },
  mounted() {
    console.log(this.purchase_point);
  },
  methods: {
    applyPurchasePoint() {
      if (this.payment_form.use_purchase_point > this.purchase_point) {
        toastr.error("You don't have enough purchase point.");
        this.payment_form.use_purchase_point = "";

        return;
      } else {
        let url = this.getUrl('user/apply-purchase-point');
        this.payment_form.trx_id = this.trx_id;

        axios.post(url, this.payment_form).then((response) => {
          if (response.data.error) {
            toastr.error("Point not found", "Error !!");
          } else {
            console.log(response.data);
            toastr.success(response.data.success, this.lang.Success + " !!");
            this.carts = [];
            let carts = response.data.carts;
            let checkouts = response.data.checkouts;
            let points = response.data.points;
            this.$parent.parseData(carts, checkouts, points);
            // this.payment_form.use_purchase_point = "";
          }
        });
      }
    },
  },
};
</script>
  
  <style scoped>
.available-point {
  border: 1px solid #eee;
  padding: 4px 8px;
  margin-bottom: 10px;
  border-radius: 5px;
  width: 200px;
  position: relative;
}

.accordion-button:not(.collapsed)::after{
    background-image: none !important;
    transform: none !important;
}
</style>
  