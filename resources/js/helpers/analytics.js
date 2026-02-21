/**
 * Analytics & Tracking Helper for Dolbear Web App
 * Works with existing data structure without modifying existing functionality
 *
 * @version 1.0.0
 * @author Dolbear Team
 */

const Analytics = {
	/**
	 * Check if tracking scripts are loaded
	 */
	isGTMReady() {
		return typeof window.dataLayer !== 'undefined';
	},

	isFacebookReady() {
		return typeof window.fbq !== 'undefined';
	},

	/**
	 * Get active currency code
	 */
	getCurrencyCode(activeCurrency) {
		if (activeCurrency && activeCurrency.code) {
			return activeCurrency.code;
		}
		return 'BDT'; // Default fallback - Bangladeshi Taka
	},

	/**
	 * ============================================
	 * PRODUCT VIEW TRACKING
	 * ============================================
	 * Call this when product details page loads
	 */
	trackProductView(productDetails, activeCurrency) {
		if (!this.isGTMReady() || !productDetails) {
			console.warn('[Analytics] GTM not ready or no product data');
			return;
		}

		try {
			// Get the actual price (use sale_price if available, otherwise regular price)
			const price = productDetails.sale_price || productDetails.product_stock?.price || productDetails.price || 0;

			// Get SKU from product_stock
			const sku = productDetails.product_stock?.sku || productDetails.sku || String(productDetails.id);

			// Get category name
			const category = productDetails.category_name || productDetails.category?.name || '';

			const currency = this.getCurrencyCode(activeCurrency);

			// GA4 Enhanced Ecommerce - View Item
			window.dataLayer.push({
				event: 'view_item',
				ecommerce: {
					currency: currency,
					value: parseFloat(price),
					items: [{
						item_id: sku,
						item_name: productDetails.product_name || '',
						item_category: category,
						price: parseFloat(price),
						quantity: 1
					}]
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			// Error tracking product view
		}
	},

	/**
	 * ============================================
	 * ADD TO CART TRACKING
	 * ============================================
	 * Call this when user adds product to cart
	 */
	trackAddToCart(productDetails, quantity, activeCurrency) {
		if (!this.isGTMReady() || !productDetails) {
			return;
		}

		try {
			const price = productDetails.sale_price || productDetails.product_stock?.price || productDetails.price || 0;
			const sku = productDetails.product_stock?.sku || productDetails.sku || String(productDetails.id);
			const category = productDetails.category_name || productDetails.category?.name || '';
			const currency = this.getCurrencyCode(activeCurrency);
			const totalValue = parseFloat(price) * parseInt(quantity);

			// GA4 - Add to Cart
			window.dataLayer.push({
				event: 'add_to_cart',
				ecommerce: {
					currency: currency,
					value: totalValue,
					items: [{
						item_id: sku,
						item_name: productDetails.product_name || '',
						item_category: category,
						price: parseFloat(price),
						quantity: parseInt(quantity)
					}]
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * BEGIN CHECKOUT TRACKING
	 * ============================================
	 * Call this when user enters checkout page
	 */
	trackBeginCheckout(carts, couponCode, activeCurrency) {
		if (!this.isGTMReady() || !carts || !Array.isArray(carts) || carts.length === 0) {
			return;
		}

		try {
			const currency = this.getCurrencyCode(activeCurrency);
			const totalValue = carts.reduce((sum, item) => {
				return sum + (parseFloat(item.price) * parseInt(item.quantity));
			}, 0);

			const items = carts.map(cart => ({
				item_id: cart.sku || String(cart.product_id),
				item_name: cart.product_name || '',
				item_category: cart.category_name || '',
				price: parseFloat(cart.price),
				quantity: parseInt(cart.quantity)
			}));

			// GA4 - Begin Checkout
			window.dataLayer.push({
				event: 'begin_checkout',
				ecommerce: {
					currency: currency,
					value: totalValue,
					coupon: couponCode || '',
					items: items
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * VIEW CART TRACKING
	 * ============================================
	 * Call this when user views shopping cart
	 */
	trackViewCart(carts, activeCurrency) {
		if (!this.isGTMReady() || !carts || !Array.isArray(carts) || carts.length === 0) {
			return;
		}

		try {
			const currency = this.getCurrencyCode(activeCurrency);
			const totalValue = carts.reduce((sum, item) => {
				return sum + (parseFloat(item.price - (item.discount || 0)) * parseInt(item.quantity));
			}, 0);

			const items = carts.map(cart => ({
				item_id: cart.sku || String(cart.product_id),
				item_name: cart.product_name || '',
				item_variant: cart.variant || '',
				item_category: cart.category_name || '',
				price: parseFloat(cart.price - (cart.discount || 0)),
				quantity: parseInt(cart.quantity)
			}));

			// GA4 - View Cart
			window.dataLayer.push({
				event: 'view_cart',
				ecommerce: {
					currency: currency,
					value: totalValue,
					items: items
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * REMOVE FROM CART TRACKING
	 * ============================================
	 * Call this when user removes item from cart
	 */
	trackRemoveFromCart(product, quantity, activeCurrency) {
		if (!this.isGTMReady() || !product) {
			return;
		}

		try {
			const currency = this.getCurrencyCode(activeCurrency);
			const price = parseFloat(product.price - (product.discount || 0));
			const value = price * parseInt(quantity);

			// GA4 - Remove from Cart
			window.dataLayer.push({
				event: 'remove_from_cart',
				ecommerce: {
					currency: currency,
					value: value,
					items: [{
						item_id: product.sku || String(product.product_id),
						item_name: product.product_name || '',
						item_variant: product.variant || '',
						item_category: product.category_name || '',
						price: price,
						quantity: parseInt(quantity)
					}]
				}
			});
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * PURCHASE TRACKING
	 * ============================================
	 * Call this when order is completed (in get-invoice.vue)
	 */
	trackPurchase(orders, activeCurrency) {
		if (!this.isGTMReady() || !orders || !Array.isArray(orders) || orders.length === 0) {
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

				// NOTE: Facebook Pixel tracking removed to prevent duplicates
				// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
			});
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * SEARCH TRACKING
	 * ============================================
	 * Call this when user performs a search
	 */
	trackSearch(searchTerm) {
		if (!this.isGTMReady() || !searchTerm || searchTerm.trim().length === 0) {
			return;
		}

		try {
			// GA4 - Search
			window.dataLayer.push({
				event: 'search',
				search_term: searchTerm.trim()
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * WISHLIST TRACKING
	 * ============================================
	 * Call this when user adds product to wishlist
	 */
	trackAddToWishlist(productDetails, activeCurrency) {
		if (!this.isGTMReady() || !productDetails) {
			return;
		}

		try {
			const price = productDetails.sale_price || productDetails.product_stock?.price || productDetails.price || 0;
			const sku = productDetails.product_stock?.sku || productDetails.sku || String(productDetails.id);
			const category = productDetails.category_name || productDetails.category?.name || '';
			const currency = this.getCurrencyCode(activeCurrency);

			// GA4 - Add to Wishlist
			window.dataLayer.push({
				event: 'add_to_wishlist',
				ecommerce: {
					currency: currency,
					value: parseFloat(price),
					items: [{
						item_id: sku,
						item_name: productDetails.product_name || '',
						item_category: category,
						price: parseFloat(price),
						quantity: 1
					}]
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * PAGE VIEW TRACKING
	 * ============================================
	 * Call this for custom page tracking
	 */
	trackPageView(pageName, pageCategory = '') {
		if (!this.isGTMReady()) {
			return;
		}

		try {
			window.dataLayer.push({
				event: 'page_view',
				page_title: pageName,
				page_category: pageCategory
			});
		} catch (error) {
			console.error(error);
		}
	},

	/**
	 * ============================================
	 * CAMPAIGN TRACKING
	 * ============================================
	 * Call this when user views a campaign/event
	 */
	trackCampaignView(campaign) {
		if (!this.isGTMReady() || !campaign) {
			return;
		}

		try {
			// GA4 Custom Event
			window.dataLayer.push({
				event: 'view_campaign',
				campaign: {
					id: String(campaign.id),
					name: campaign.title || campaign.name || '',
					type: campaign.type || 'campaign'
				}
			});

			// NOTE: Facebook Pixel tracking removed to prevent duplicates
			// GTM container (GTM-54BWTWX9) handles Facebook Pixel based on dataLayer events
		} catch (error) {
			console.error(error);
		}
	}
};

// Export for use in Vue components
if (typeof module !== 'undefined' && module.exports) {
	module.exports = Analytics;
}

// ES6 export for modern JavaScript
export default Analytics;
