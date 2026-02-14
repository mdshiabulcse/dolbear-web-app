<template>
	<!-- <div class="sg-page-content">
		<section class="after-track-order text-center" v-if="is_shimmer">
			<div class="container" v-for="(order, index) in orders" :key="index">
				<div class="invoice_border mt-2">
					<div class="page-title">
						<h1>{{ lang.thank_you_for_purchase }}</h1>
						<p v-if="authUser"
							>{{ lang.a_copy_summary_has_been_sent_to }} <a :href="'mailto' + authUser.email">{{ authUser.email }}</a></p
						>
						<h2>{{ lang.order_id }} {{ order.code }}</h2>
					</div>
					<div class="step-content">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th scope="col" class="text-start">{{ lang.product_name }}</th>
                    <th scope="col" class="text-end">{{ lang.price }}</th>
                    <th scope="col" class="text-end">{{ lang.quantity }}</th>
										<th scope="col" class="text-end">{{ lang.sub_total }}</th>
                    <th scope="col" class="text-end">{{ lang.discount }}</th>
										<th scope="col" class="text-end">{{ lang.total_amount }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(order_detail, index) in order.order_details" :key="'order' + index">
										<td>
											<div class="product-name">
												<p>{{ order_detail.product_name }} <span v-if="order_detail.variation">({{ order_detail.variation }})</span></p>
											</div>
										</td>
                    <td class="text-end">{{ priceFormat(order_detail.price) }}</td>
                    <td class="text-end">{{ order_detail.quantity }}</td>
										<td class="text-end">{{ priceFormat(order_detail.price) }}
                      X {{ order_detail.quantity }}
                      = {{ priceFormat(order_detail.price * order_detail.quantity) }}</td>


                    <td v-if="(order_detail.discount * order_detail.quantity) > 0" class="text-end">
                      {{ priceFormat(order_detail.discount) }}
                      X {{ order_detail.quantity }}
                      = {{ priceFormat(order_detail.discount * order_detail.quantity) }}
                    </td>
                    <td v-else class="text-end">{{ priceFormat(0) }}</td>
                    <td class="text-end">{{
											priceFormat(
												((parseFloat(order_detail.price) * order_detail.quantity) +	(parseFloat(order_detail.tax) * order_detail.quantity) +parseFloat(order_detail.shipping_cost.total_cost)) -
													((parseFloat(order_detail.discount) * order_detail.quantity) + parseFloat(order_detail.coupon_discount.discount)),
											)
										}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="account-table">
							<div class="title">
								<h1>{{ lang.account_details }}</h1>
							</div>
							<div class="table-responsive">
								<table class="table text-start">
									<tbody>
										<tr>
											<td>
												<ul class="global-list">
													<li>{{ lang.order_code }} </li>
													<li v-if="authUser">{{ lang._name }} </li>
													<li v-if="authUser">{{ lang._email }}</li>
													<li>Delivery Address</li>
												</ul>
											</td>
											<td>
												<ul class="global-list">
													<li>{{ order.code }}</li>
													<li v-if="authUser">{{ authUser.full_name }}</li>
													<li v-if="authUser">{{ authUser.email }}</li>
													<li>{{ order.shipping_address.address }}</li>
												</ul>
											</td>
											<td>
												<ul class="global-list">
													<li>{{ lang.order_date }}</li>
													<li>{{ lang.order_status }}</li>
													<li>{{ lang.payment_status }}</li>
													<li>{{ lang.payment_type }}</li>
												</ul>
											</td>
											<td>
												<ul class="global-list">
													<li>{{ order.date }}</li>
													<li class="text-capitalize">{{ order.delivery_status }}</li>
													<li class="text-capitalize">{{ order.payment_status }}</li>
													<li class="text-capitalize">{{ order.payment_type.replaceAll("_", " ") }}</li>
												</ul>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="row">
								<div class="col-md-4 offset-md-8">
									<div class="order-summary">
										<div class="sg-card">
                      <ul class="global-list">
                        <li>{{ lang.subtotal }} <span>{{ priceFormat(order.sub_total) }}</span></li>
                        <li v-if="order.tax_type == 'before_tax' || order.vat_tax_type == 'product_base'">{{ lang.tax }} <span>{{ priceFormat(order.total_tax) }}</span></li>
                        <li>{{ lang.discount }}<span>{{ priceFormat(order.discount) }}</span>
                        </li>
                        <li v-if="settings.shipping_cost != 'area_base' || $route.name != 'cart'">{{ lang.shipping_cost }}<span>{{
                            priceFormat(order.shipping_cost)
                          }}</span></li>
                        <li v-if="settings.coupon_system == 1">{{ lang.coupon_discount }}<span>{{
                            priceFormat(order.coupon_discount)
                          }}</span></li>
                      </ul>
                      <div class="order-total text-left" v-if="settings.tax_type == 'after_tax' && settings.vat_and_tax_type == 'order_base'">
                        <p class="font_weight_400">{{ lang.total }} <span>{{ priceFormat((parseFloat(order.sub_total) + parseFloat(order.shipping_cost)) - (parseFloat(order.coupon_discount) + parseFloat(order.discount))) }}</span></p>
                        <p class="font_weight_400">{{ lang.tax }} <span>{{ priceFormat(order.total_tax) }}</span></p>
                        <p class="grand_total_style">{{ lang.grand_total }} <span>{{ priceFormat(order.total_payable) }}</span></p>
                      </div>
                      <div class="order-total" v-else>
                        <p>{{ lang.total }} <span>{{ priceFormat(order.total_payable) }}</span></p>
                      </div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row justifycom-content-center">
					<div class="col-lg-12">
						<loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
						<a href="javascript:void(0)" v-else class="btn btn-primary" @click="download(order.id, order.code)">{{ lang.download }} {{ lang.invoice }}</a>
					</div>
				</div>
			</div> </section
		>
		<section v-else-if="shimmer">
			<div class="container">
				<div class="page-title">
					<shimmer class="mb-3" v-for="(order, index) in 3" :key="index" :height="20"></shimmer>
				</div>
				<table class="table">
					<div class="step-content">
						<div class="table-responsive">
							<shimmer :height="50"></shimmer>
						</div>
						<div class="account-table">
							<div class="table-responsive">
								<shimmer :height="155"></shimmer>
							</div>
						</div>
					</div>
				</table>
				<div class="row">
					<div class="col-md-4 offset-md-8">
						<shimmer :height="288"></shimmer>
					</div>
				</div>
			</div>
		</section> 
	</div> -->

	<div id="container" class="container">
		<div class="main_container" v-for="(order, index) in orders" :key="index">
			<div class=" w-100 h-auto d-flex justify-content-between ">
				<div class="fs-2 fw-semibold">Your Order Complete</div>
				<div class="fs-3">dolbear </div>
			</div>
			<div class="fs-4 fw-semibold">Order Details</div>
			<div class="fs-4 fw-medium" v-if="authUser">Hi {{ authUser.full_name }},</div>
			<div class="my-3">Just to let you know - we've received your  <span class="fw-medium">{{ lang.order_id }} {{ order.code }}</span>  and it is now being processed: Pay with cash upon delivery.</div>
			<div class="mb-3 fs-4">[Order {{ order.code }}] ({{ order.date }})</div>

			<div class="table_container">
				<div class="table_header" style="background-color:gray;">
					<div class="product_column">Product</div>
					<div class="quantity_column">Quantity</div>
					<div class="price_column">Price</div>
					<div class="price_column">Discount</div>
					<div class="price_column">Total </div>
				</div>
				<div >
					<div class="w-100" v-for="(order_detail, index) in order.order_details" :key="'order' + index">
						<div class="table_body">
						<div class="product_data">{{ order_detail.product_name }} <span v-if="order_detail.variation"> ({{ order_detail.variation }})</span> </div>
						<div class="quantity_data">{{ order_detail.quantity }}</div>
						<div class="price_data">{{ priceFormat(order_detail.price) }}</div>
						<div class="price_data">{{ priceFormat(order_detail.discount * order_detail.quantity) }}</div>
						<div class="price_data">{{								priceFormat(												((parseFloat(order_detail.price) * order_detail.quantity) +	(parseFloat(order_detail.tax) * order_detail.quantity) +parseFloat(order_detail.shipping_cost.total_cost)) -													((parseFloat(order_detail.discount) * order_detail.quantity) + parseFloat(order_detail.coupon_discount.discount)),											)										}}</div>
					</div>
					
					</div>
					
					<div class="table_body" style="display: block;">
						<div class="w-100">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Subtotal</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> {{ priceFormat(order.sub_total) }}</div>
							</div>
						</div>
						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title"> Discount</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> {{ priceFormat(order.discount) }}</div>
							</div>
						</div>
						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Promo Discount</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> {{
                            priceFormat(order.coupon_discount)
                          }}</div>
							</div>
						</div>
						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Shipping</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> 
									<div v-if="order.delivery_method === 'Pick from Store'">
											<p class="mb-0 text-break">{{ order.store.name }}</p> 
											<p class="mb-0 text-break">{{ order.store.address }}</p>
											
										</div>
										<div v-else>  
												{{
													priceFormat(order.shipping_cost)
												}} (up to 3 business days)
										</div>
						  	</div>
							</div>
						</div>

						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Delivery Method</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> 
									{{ order.delivery_method.replaceAll("_", " ") }}
						  	</div>
							</div>
						</div>
						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Payment method</div>
									<div>:</div>
								</div>
								<div class="subtotal_amount"> 
									
									{{ order.payment_type.replaceAll("_", " ").charAt(0).toUpperCase() + order.payment_type.replaceAll("_", " ").slice(1) }}


									
								</div>
							</div>
						</div>

						<div class="w-100" style=" display: block;">
							<div class="subtotal_section">
								<div class="subtotal_title_container">
									<div class="subtotal_title">Total</div>
									<div>:</div>
								</div>
								
								<div class="subtotal_amount"> {{ priceFormat(order.total_payable) }}</div>
							</div>
						</div>
					</div>

					<div class="table_body" >
						<div class="table_billing_address">  Shipping Address</div>
						
					</div>

					<div class="table_body" >
						<div class="table_billing_addresss_details">
							Name: {{ order.shipping_address?.name || "No data found" }} <br>
							Phone No: {{ order.shipping_address?.phone_no || "No data found" }} <br>
							Email: {{ order.shipping_address?.email || "No data found" }} <br>
							Division: {{ order.shipping_address?.division || "No data found" }} <br>
							District: {{ order.shipping_address?.district || "No data found" }} <br>
							Thana: {{ order.shipping_address?.thana || "No data found" }} <br>
							Address: {{ order.shipping_address?.address || "No data found" }}
							</div>

		
					</div>

					<div class="table_button_container">
						
						<!-- <div class="invoice_button" @click="printContainerContent()">Save as PDF</div> -->
						<!-- <div class="invoice_button" >Print</div> -->
						<loading_button v-if="loading" :class_name="'invoice_button'"></loading_button>
						<a href="javascript:void(0)" v-else class="invoice_button" @click="download(order.id, order.code)">Save as PDF</a>
					</div>
					<!-- <div class="col-lg-12 mt-5">
						<loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
						<a href="javascript:void(0)" v-else class="btn btn-primary" @click="download(order.id, order.code)">Save as PDF</a>
					</div> -->

					<div class="thankyou_container">
						<div class="fs-1">Thanks for using dolbear.com.bd!</div>
						<div class="fs-1 opacity-25">Think Tech, Think Dolbear</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<style>

.main_container{
	width: 80%;
	margin: 50px auto;
}

.table_container{
	width: 100%;
	height: auto;
}

.table_header{
	display: flex;
    background-color: #656565;
    color: white;
    height: 35px;
    padding: 0px 15px;
    align-items: center;
}

.product_column{
	width: 40%;
}

.quantity_column{
	width: 15%;
	text-align: center;
}

.price_column{
	width: 15%;
	text-align: center;
}

.table_body{
	display: flex;
    height: auto;
    padding: 10px 15px;
    align-items: center;
	border-left: 1px dotted black;
	border-right: 1px dotted black;
	border-bottom: 1px dotted black;
}

.product_data{
	width: 40%;
}

.quantity_data{
	width: 15%;
	text-align: center;
}

.price_data{
	width: 15%;
	text-align: center;
}

.subtotal_section{
	width: 100%;
	height: auto;
	display: flex;
}

.subtotal_title{
	width: 100%;
}

.subtotal_amount{
	width: 60%;
	padding-left: 30px;
}

.subtotal_title_container{
	display: flex;
	width: 30%;
	justify-content: space-between;
}

.table_billing_address{
	width: 100%;
	font-weight: 700;
}

.table_shipping_address{
	width: 50%;
	font-weight: 700;
	padding-left: 20px;
}

.table_billing_addresss_details{
	width: 100%;

}

.table_shipping_address_details{
	width: 50%;
	padding-left: 20px;
}

.table_button_container{
	width: 100%;
    height: auto;
    display: flex;
    column-gap: 10px;
    justify-content: center;
    margin-top: 35px;
}

.invoice_button{
	width: 150px;
    height: auto;
    background-color: #242424;
    color: #ffff !important;
    padding: 5px 5px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
	cursor: pointer;
}

.thankyou_container{
	width: 100%;
	height: auto;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	margin-top: 35px;

}





/* Extra Small Devices (Phones) */
@media (max-width: 575px) {  
	.main_container{
	width: 100%;
  }

  .table_header{
	font-size: 12px;
	padding-left: 5px 5px;
  }

  .table_body{
	font-size: 12px;
	padding: 5px 5px;
  }

  .product_column{
	width: 40%;
  }


  .quantity_column{
	width: 15%;
	}

	.price_column{
		width: 15%;
		text-align: center;
	}


	.product_data{
		width: 40%;
	}

	.quantity_data{
		width: 15%;
		text-align: center;
		text-align: center;
	}

	.price_data{
		width: 15%;
		text-align: center;
	}


}


/* Small Devices (Tablets) */
@media (min-width: 576px) and (max-width: 767px) {  
.main_container{
	width: 100%;
  }

  .product_column{
	width: 30%;
  }


  .quantity_column{
	width: 15%;
	}

	.price_column{
		width: 15%;
		text-align: center;
	}


	.product_data{
		width: 30%;
	}

	.quantity_data{
		width: 15%;
		text-align: center;
	}

	.price_data{
		width: 15%;
		text-align: center;
	}


}

/* Medium Devices (Small Laptops) */
@media (min-width: 768px) and (max-width: 991px) {  
	.main_container{
	width: 100%;
  }
}

/* Large Devices (Desktops) */
@media (min-width: 992px) and (max-width: 1199px) {  
  /* Styles for desktops */
}

/* Extra Large Devices (Large Screens) */
@media (min-width: 1200px) {  
  /* Styles for large screens */
}

@media print {
            .table_button_container {
                display: none;
            }
        }

</style>

<script>
import shimmer from "../../partials/shimmer";

// Analytics Tracking Helper for Purchase
const Analytics = {
    isGTMReady() {
        return typeof window.dataLayer !== 'undefined';
    },
    isFacebookReady() {
        return typeof window.fbq !== 'undefined';
    },
    getCurrencyCode(activeCurrency) {
        // Default to BDT for Dolbear Bangladesh site
        return (activeCurrency && activeCurrency.code) ? activeCurrency.code : 'BDT';
    },
    trackPurchase(orders, activeCurrency) {
        if (!this.isGTMReady() || !orders || !Array.isArray(orders) || orders.length === 0) {
            console.warn('[Analytics] GTM not ready or no order data');
            return;
        }

        try {
            const currency = this.getCurrencyCode(activeCurrency);

            // Process each order
            orders.forEach(order => {
                if (!order.order_details || !Array.isArray(order.order_details)) {
                    return;
                }

                const items = order.order_details.map(detail => ({
                    item_id: detail.sku || String(detail.product_id || ''),
                    item_name: detail.product_name || '',
                    item_category: detail.category_name || '',
                    item_variant: detail.variation || '',
                    price: parseFloat(detail.price),
                    quantity: parseInt(detail.quantity)
                }));

                // Calculate totals
                const totalTax = parseFloat(order.total_tax) || 0;
                const totalShipping = parseFloat(order.shipping_cost) || 0;

                // GA4 - Purchase
                window.dataLayer.push({
                    event: 'purchase',
                    ecommerce: {
                        transaction_id: String(order.code),
                        affiliation: 'Dolbear Store',
                        value: parseFloat(order.total_payable),
                        tax: totalTax,
                        shipping: totalShipping,
                        currency: currency,
                        coupon: String(order.coupon_discount > 0 ? 'applied' : ''),
                        items: items
                    }
                });

                // NOTE: Facebook Pixel tracking handled by GTM container (GTM-54BWTWX9)
                // DO NOT call fbq('track', 'Purchase') directly - GTM will handle it

                console.log('[Analytics] Purchase tracked for order:', order.code, 'Total:', order.total_payable, 'Currency:', currency);
            });
        } catch (error) {
            console.error('[Analytics] Error tracking purchase:', error);
        }
    }
};

export default {
	name: "get-invoice.vue",
	data() {
		return {
			orders: [],
			is_shimmer: false,
			loading: false,
			purchaseTracked: false,
		};
	},
	mounted() {
		this.getInvoices();
	},
	components: {
		shimmer,
	},
	computed: {
		shimmer() {
			return this.$store.state.module.shimmer;
		},
	},
	methods: {
		 printContainerContent() {
			var printContents = document.querySelector("#container").innerHTML;
			var originalContents = document.body.innerHTML;
			
			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
			},
		getInvoices() {
			this.$Progress.start();
			let url = this.getUrl("user/get-invoices/" + this.$route.params.trx_id);
			axios
				.get(url)
				.then((response) => {
					this.is_shimmer = true;
					if (response.data.error) {
						this.$Progress.fail();
						toastr.error(response.data.error, this.lang.Error + " !!");
					} else {
						this.$Progress.finish();
						if (!response.data.guest) {
							this.$store.dispatch("user", response.data.user);
						}
						this.$store.dispatch("compareList", response.data.compare_list);
						this.$store.dispatch("wishlists", response.data.wishlists);
						// this.$store.dispatch("carts", 0);

						let orders = response.data.orders;
						this.orders = orders;
						if (orders) {
							for (let i = 0; i < orders.length; i++) {
								this.payment_form.sub_total += orders[i].total_amount;
								this.payment_form.discount_offer += orders[i].discount;
								this.payment_form.shipping_tax += orders[i].shipping_cost;
								this.payment_form.tax += orders[i].total_tax;
								if (this.settings.coupon_system == 1) {
									this.payment_form.coupon_discount += orders[i].coupon_discount;
								}
								this.trx_id = orders[i].trx_id;
							}

							if (orders[0].is_mailed == 0) {
								this.sendMail();
							}

							this.payment_form.total =
								this.payment_form.sub_total +
								this.payment_form.tax +
								this.payment_form.shipping_tax -
								(this.payment_form.discount_offer + this.payment_form.coupon_discount);

							// Analytics: Track purchase once when order data is loaded
							if (!this.purchaseTracked) {
								this.$nextTick(() => {
									Analytics.trackPurchase(orders, this.active_currency);
									this.purchaseTracked = true;
								});
							}
						}
					}
				})
				.catch((error) => {
					this.$Progress.finish();
				});
		},
		sendMail() {
			let url = this.getUrl("user/mail-order/" + this.trx_id);
			axios.get(url).then((response) => {
				// this.sendMailSeller();
			});
		},
		sendMailSeller() {
			let url = this.getUrl("user/mail-order-seller/" + this.trx_id);
			axios.get(url).then((response) => {});
		},
		download(id, code) {
			this.loading = true;
			axios
				.get(this.getUrl("user/invoice/download/" + id), { responseType: "arraybuffer" })
				.then((response) => {
					this.loading = false;
					1;
					if (response.data.error) {
						toastr.error(response.data.error, this.lang.Error + " !!");
					} else {
						let blob = new Blob([response.data], { type: "application/pdf" });
						let link = document.createElement("a");
						link.href = window.URL.createObjectURL(blob);
						link.download = code + ".pdf";
						link.click();
					}
				})
				.catch((error) => {
					this.loading = false;
					toastr.error(error.response.statusText, this.lang.Error + " !!");
				});
		},
	},
};
</script>
