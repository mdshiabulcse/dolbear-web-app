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
		return 'USD'; // Default fallback
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

			// Facebook Pixel - ViewContent
			if (this.isFacebookReady()) {
				window.fbq('track', 'ViewContent', {
					content_name: productDetails.product_name || '',
					content_category: category,
					content_ids: [sku],
					content_type: 'product',
					value: parseFloat(price),
					currency: currency
				});
			}

			console.log('[Analytics] Product view tracked:', productDetails.product_name);
		} catch (error) {
			console.error('[Analytics] Error tracking product view:', error);
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
			console.warn('[Analytics] GTM not ready or no product data');
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

			// Facebook Pixel - AddToCart
			if (this.isFacebookReady()) {
				window.fbq('track', 'AddToCart', {
					content_name: productDetails.product_name || '',
					content_category: category,
					content_ids: [sku],
					content_type: 'product',
					value: totalValue,
					currency: currency
				});
			}

			console.log('[Analytics] Add to cart tracked:', productDetails.product_name, 'Qty:', quantity);
		} catch (error) {
			console.error('[Analytics] Error tracking add to cart:', error);
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
			console.warn('[Analytics] GTM not ready or no cart data');
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

			// Facebook Pixel - InitiateCheckout
			if (this.isFacebookReady()) {
				window.fbq('track', 'InitiateCheckout', {
					content_ids: carts.map(c => c.sku || String(c.product_id)),
					content_type: 'product',
					value: totalValue,
					currency: currency,
					num_items: carts.reduce((sum, item) => sum + parseInt(item.quantity), 0)
				});
			}

			console.log('[Analytics] Begin checkout tracked. Total:', totalValue, 'Items:', carts.length);
		} catch (error) {
			console.error('[Analytics] Error tracking begin checkout:', error);
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

				// Facebook Pixel - Purchase
				if (this.isFacebookReady()) {
					window.fbq('track', 'Purchase', {
						content_ids: order.order_details.map(d => d.sku || String(d.product_id || '')),
						content_type: 'product',
						value: parseFloat(order.total_payable),
						currency: currency,
						num_items: order.order_details.length
					});
				}

				console.log('[Analytics] Purchase tracked for order:', order.code, 'Total:', order.total_payable);
			});
		} catch (error) {
			console.error('[Analytics] Error tracking purchase:', error);
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

			// Facebook Pixel - Search
			if (this.isFacebookReady()) {
				window.fbq('track', 'Search', {
					search_string: searchTerm.trim(),
					content_category: 'Products'
				});
			}

			console.log('[Analytics] Search tracked:', searchTerm);
		} catch (error) {
			console.error('[Analytics] Error tracking search:', error);
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

			// Facebook Pixel - AddToWishlist
			if (this.isFacebookReady()) {
				window.fbq('track', 'AddToWishlist', {
					content_name: productDetails.product_name || '',
					content_category: category,
					content_ids: [sku],
					content_type: 'product',
					value: parseFloat(price),
					currency: currency
				});
			}

			console.log('[Analytics] Add to wishlist tracked:', productDetails.product_name);
		} catch (error) {
			console.error('[Analytics] Error tracking add to wishlist:', error);
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

			console.log('[Analytics] Page view tracked:', pageName);
		} catch (error) {
			console.error('[Analytics] Error tracking page view:', error);
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

			// Facebook Custom Event
			if (this.isFacebookReady()) {
				window.fbq('trackCustom', 'ViewCampaign', {
					campaign_name: campaign.title || campaign.name || '',
					campaign_id: String(campaign.id),
					campaign_type: campaign.type || 'campaign'
				});
			}

			console.log('[Analytics] Campaign view tracked:', campaign.title || campaign.name);
		} catch (error) {
			console.error('[Analytics] Error tracking campaign view:', error);
		}
	}
};

// Export for use in Vue components
if (typeof module !== 'undefined' && module.exports) {
	module.exports = Analytics;
}
