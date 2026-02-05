<template>
    <div class="container track-order-container" :style="{ height: orderData ? 'auto' : '47vh' }">
        <h4>Track your Order</h4>
        <p class="text-muted small mb-3">Enter your order code (e.g., #30124) to track your order</p>
        <div class="d-flex mb-2 gap-2">
            <input
                type="text"
                placeholder="Enter your order code (e.g., #30124)"
                class="track-order-input"
                v-model="searchOrder"
                @keyup.enter="trackOrder"
            >
            <button class="track-order-btn" @click="trackOrder" :disabled="loading">
                {{ loading ? 'Searching...' : 'Search' }}
            </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
            {{ errorMessage }}
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="alert alert-success" role="alert">
            {{ successMessage }}
        </div>

        <div class="d-flex justify-content-center" style="color: #168FC3;">
<!--            <h4>Unlock 5% Off Your Second Order!</h4>-->
        </div>
        <div class="d-flex justify-content-between sec-order-no mb-2" v-if="orderData">
            <p><strong>Order No:</strong> {{ orderData?.code }}</p>
            <p><strong>Status:</strong> {{ orderData?.delivery_status?.toUpperCase().replace(/_/g, ' ') }}</p>
        </div>

        <div class="d-flex justify-content-between product-info-sec mb-2"
            v-for="(orderItem, index) in orderData?.order_details" :key="index">
            <div class="d-flex" style="width: 40%;">
                <img :src="orderItem?.product?.image_72x72" alt="">
                <div class="d-flex flex-column justify-content-between">
                    <div>
                        <p>{{ orderItem?.product?.product_name }}</p>
                        <p v-if="orderItem?.product?.has_variant">Variant: {{ orderItem?.variation }}</p>
                    </div>

                    <p v-if="orderItem?.product?.warrenty && orderItem?.product?.warrenty !== null">{{ orderItem?.product?.warrenty }} Brand Warranty</p>
                </div>
            </div>
            <p>৳ {{ orderItem?.price }}</p>
            <p>Qty: {{ orderItem?.quantity }}</p>
            <p>৳ {{ parseInt(orderItem?.quantity) * parseInt(orderItem?.price) }}</p>
        </div>

        <div class="sec-order-no mb-2" v-if="orderData?.shipping_address">
            <p>Name: {{ orderData?.shipping_address?.name }}</p>
            <!-- <p>Email: {{ orderData?.shipping_address?.email }}</p>
            <p>Address: {{ orderData?.shipping_address?.address }} <span v-if="orderData?.shipping_address?.address">,</span> {{ orderData?.shipping_address?.thana }}, {{
                orderData?.shipping_address?.district }}, {{ orderData?.shipping_address?.division }} </p>
            <p>Phone No: {{ orderData?.shipping_address?.phone_no }}</p> -->
        </div>
        <div class="d-flex flex-column flex-md-row gap-2"
            v-if="orderData?.delivery_histories && orderData.delivery_histories.length">
            <div class="tracking-details p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Tracking Details</h5>
                    <span class="tracking-id">{{ orderData?.code }}</span>
                </div>

                <ul class="timeline list-unstyled mt-3">
                    <li class="timeline-item" v-for="(history, index) in orderData.delivery_histories" :key="history.id"
                        :class="{ active: index === 0 }">

                        <div class="date-time">
                            {{ formatDate(history.created_at) }}<br />{{ formatTime(history.created_at) }}
                        </div>
                        <div class="status-line">
                            <span class="dot"></span>
                        </div>
                        <div class="status">
                            <p>{{ getEventName(history.event) }}</p>
                            <p>{{ getEventMessage(history.event) }}</p>
                        </div>
                    </li>
                </ul>

            </div>
            <div class="product-summary p-3 border rounded" v-if="orderData">
                <h4>Total Summary</h4>

                <ul>
                    <li>
                        <p> SubTotal:</p>
                        <p>৳ {{ orderData?.sub_total }}</p>
                    </li>
                    <li>
                        <p>Shipping Fee:</p>
                        <p>৳ {{ orderData?.shipping_cost }}</p>
                    </li>
                    <li>
                        <p>Discount:</p>
                        <p>৳ {{ orderData?.discount }}</p>
                    </li>

                    <li>
                        <p>Coupon Discount:</p>
                        <p>৳ {{ orderData?.coupon_discount }}</p>
                    </li>

                    <li class="mt-3">
                        <p>Total:</p>
                        <p>৳ {{ orderData?.total_payable }}</p>
                    </li>
                </ul>

            </div>

        </div>
    </div>
</template>

<script>

export default {

    data() {
        return {
            searchOrder: "",
            orderData: "",
            loading: false,
            errorMessage: "",
            successMessage: "",

        }
    },

    async created() {

    },

    async mounted() {
        const code = this.$route.query.code;

        if(code){
            this.searchOrder = code;
            await this.trackOrder();
        }

    },

    methods: {
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
        },
        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" });
        },
        getEventName(event) {
            const eventNames = {
              order_no_ans_3_event: "Awaiting Confirmation",
              order_no_ans_2_event: "Awaiting Confirmation",
              order_no_ans_1_event: "Awaiting Confirmation",
                order_canceled_event: "Order Has Canceled",
                order_delivered_event: "Order Has Delivered",
                order_on_the_way_event: "Order On The Way",
                order_picked_up_event: "Order Picked Up",
                order_confirm_event: "Order Confirmed",
                order_create_event: "Order Created",
            };
            return eventNames[event] || "Unknown Event";
        },
        getEventMessage(event) {
            const eventMessages = {
              order_no_ans_3_event: "Your order has been awaiting for confirmation",
              order_no_ans_2_event: "Your order has been awaiting for confirmation",
              order_no_ans_1_event: "Your order has been awaiting for confirmation",
                order_canceled_event: "Your order has been canceled.",
                order_delivered_event: "Your order has been delivered.",
                order_on_the_way_event: "Your order is on the way.",
                order_picked_up_event: "Your order has been Picked Up.",
                order_confirm_event: "Your order has been confirmed.",
                order_create_event: "Your order has been created and is being processed.",

            };
            return eventMessages[event] || "No additional information available.";
        },
        async trackOrder() {
            // Clear previous messages
            this.errorMessage = "";
            this.successMessage = "";
            this.orderData = "";

            // Validate input
            if (!this.searchOrder || this.searchOrder.trim() === "") {
                this.errorMessage = "Please enter an order code or ID.";
                return;
            }

            this.loading = true;

            try {
                const baseUrl = `${window.location.protocol}//${window.location.host}`;

                const response = await axios.post(`${baseUrl}/track-order`, {
                    order_id: this.searchOrder.trim(),
                });

                if (response.data?.order) {
                    this.orderData = response.data.order;
                    this.successMessage = "Order found successfully!";
                } else if (response.data?.error) {
                    this.errorMessage = response.data.error;
                } else {
                    this.errorMessage = "Order not found. Please check your order code or ID and try again.";
                }
            } catch (error) {
                console.error('Track order error:', error);
                if (error.response?.data?.error) {
                    this.errorMessage = error.response.data.error;
                } else if (error.response?.data?.message) {
                    this.errorMessage = error.response.data.message;
                } else {
                    this.errorMessage = "An error occurred while tracking your order. Please try again later.";
                }
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style>
.track-order-container {
    max-width: 812px;
}

.track-order-container h4 {
    font-size: 20px;
    font-weight: 700;
    line-height: 39px;
}

.track-order-container .small {
    font-size: 14px;
    font-weight: 400;
    color: #6c757d;
}

/* Alert styles */
.alert {
    padding: 12px 16px;
    margin-bottom: 16px;
    border-radius: 5px;
    font-size: 14px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.product-info-sec img {
    width: 95px;
    height: 95px;
}

.track-order-container input {
    width: 88%;
    height: 30px;
    border-radius: 5px;
    border: 0.1px solid #9b9b9b;
    padding-left: 8px;
    border-right: none;

}

.track-order-container .track-order-btn {
    width: 100px;
    height: 30px;
    border-radius: 5px;
    background: #6CC9F0;
    color: white;
    border: none;
    box-shadow: 0px 3px 3px 0px #00000029;
    text-align: center;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.track-order-container .track-order-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.track-order-container p {
    margin: 0px;
    font-size: 15px;
    font-weight: 600;
}

.sec-order-no {
    border: 0.1px solid #014F71;
    background: #D5F2FF;
    border-radius: 5px;
    padding: 5px 10px;
}

.product-info-sec {
    border: 1px solid #014F71;
    background: transparent;
    border-radius: 5px;
    padding-right: 10px;
}












.tracking-details {
    background-color: #f9f9f9;
    width: 65%;
}

.tracking-id {
    font-weight: bold;
}

.timeline {
    position: relative;
}

.timeline-item {
    display: flex;
    align-items: center;
    position: relative;
}

.date-time {
    width: 100px;
    text-align: right;
    color: #6c757d;
    font-size: 14px;
    font-weight: 600;
    margin-right: 20px;
}

.status-line {
    width: 2px;
    height: 13vh;
    /* height: 58px; */
    /* Fixed height for line */
    background-color: #d3d3d3;
    position: relative;
    display: flex;
    align-items: center;
}

.status-line .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #d3d3d3;
    position: absolute;
    top: 50%;
    /* Center dot vertically */
    left: 50%;
    transform: translate(-50%, -50%);
}

.timeline-item.active .status-line {
    background-color: #007bff;
}

.timeline-item.active .status-line .dot {
    background-color: #007bff;
}

.status {
    margin-left: 20px;
    color: #6c757d;
    width: 75%;
}

.product-summary p {
    color: #717171;
}

.product-summary {
    background: white;
    width: 35%;
}

.product-summary ul {
    list-style: none;
    padding: 0px;
    margin: 0px;
}

.product-summary ul li {
    display: flex;
    justify-content: space-between;
    padding: 5px 0px;
}

@media screen and (max-width: 430px) {
    .tracking-details {
        background-color: #FFFFFF;
        width: 100%;
    }

    .product-summary {
        width: 100%;
    }
}
</style>