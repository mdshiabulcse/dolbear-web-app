/**
 * Analytics & Tracking Helper
 * Handles all GTM and Facebook Pixel events for Dolbear Web App
 *
 * @version 1.0.0
 * @author Dolbear Team
 */

const Tracking = {
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
	 * GA4 E-commerce Events
	 */

	/**
	 * Track when user views a product
	 * @param {Object} product - Product object
	 * @param {number|string} product.id - Product ID
	 * @param {string} product.sku - Product SKU
	 * @param {string} product.name - Product name
	 * @param {number} product.price - Product price
	 * @param {string} product.category - Product category
	 * @param {string} product.variant - Product variant
	 * @param {string} product.currency - Currency code
	 */
	viewItem(product) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'view_item',
				ecommerce: {
					currency: product.currency || 'USD',
					value: parseFloat(product.price),
					items: [{
						item_id: product.sku || String(product.id),
						item_name: product.name,
						item_category: product.category || '',
						item_variant: product.variant || '',
						price: parseFloat(product.price),
						quantity: 1
					}]
				}
			});

			console.log('[Tracking] view_item event sent:', product);
		} catch (error) {
			console.error('[Tracking] Error sending view_item event:', error);
		}

		// Also track for Facebook
		this.fbViewContent(product);
	},

	/**
	 * Track when user adds product to cart
	 * @param {Object} product - Product object
	 * @param {number} quantity - Quantity added
	 */
	addToCart(product, quantity = 1) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		const totalValue = parseFloat(product.price) * quantity;

		try {
			window.dataLayer.push({
				event: 'add_to_cart',
				ecommerce: {
					currency: product.currency || 'USD',
					value: totalValue,
					items: [{
						item_id: product.sku || String(product.id),
						item_name: product.name,
						item_category: product.category || '',
						item_variant: product.variant || '',
						price: parseFloat(product.price),
						quantity: quantity
					}]
				}
			});

			console.log('[Tracking] add_to_cart event sent:', { product, quantity });
		} catch (error) {
			console.error('[Tracking] Error sending add_to_cart event:', error);
		}

		this.fbAddToCart(product, quantity);
	},

	/**
	 * Track when user removes item from cart
	 * @param {Object} product - Product object
	 * @param {number} quantity - Quantity removed
	 */
	removeFromCart(product, quantity = 1) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'remove_from_cart',
				ecommerce: {
					currency: product.currency || 'USD',
					value: parseFloat(product.price) * quantity,
					items: [{
						item_id: product.sku || String(product.id),
						item_name: product.name,
						item_category: product.category || '',
						price: parseFloat(product.price),
						quantity: quantity
					}]
				}
			});

			console.log('[Tracking] remove_from_cart event sent:', { product, quantity });
		} catch (error) {
			console.error('[Tracking] Error sending remove_from_cart event:', error);
		}
	},

	/**
	 * Track when user starts checkout
	 * @param {Array} cartItems - Array of cart items
	 * @param {string} coupon - Coupon code
	 */
	beginCheckout(cartItems, coupon = '') {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		const totalValue = cartItems.reduce((sum, item) => {
			return sum + (parseFloat(item.price) * item.quantity);
		}, 0);

		try {
			window.dataLayer.push({
				event: 'begin_checkout',
				ecommerce: {
					currency: 'USD',
					value: totalValue,
					coupon: coupon,
					items: cartItems.map(item => ({
						item_id: item.sku || String(item.id),
						item_name: item.name,
						item_category: item.category || '',
						item_variant: item.variant || '',
						price: parseFloat(item.price),
						quantity: item.quantity
					}))
				}
			});

			console.log('[Tracking] begin_checkout event sent:', { totalValue, itemCount: cartItems.length });
		} catch (error) {
			console.error('[Tracking] Error sending begin_checkout event:', error);
		}

		this.fbInitiateCheckout(cartItems, totalValue);
	},

	/**
	 * Track completed purchase
	 * @param {Object} orderData - Order data object
	 */
	purchase(orderData) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'purchase',
				ecommerce: {
					transaction_id: String(orderData.order_id),
					affiliation: 'Dolbear Store',
					value: parseFloat(orderData.total),
					tax: parseFloat(orderData.tax) || 0,
					shipping: parseFloat(orderData.shipping) || 0,
					currency: orderData.currency || 'USD',
					coupon: orderData.coupon || '',
					items: orderData.items.map(item => ({
						item_id: item.sku || String(item.id),
						item_name: item.name,
						item_category: item.category || '',
						item_variant: item.variant || '',
						price: parseFloat(item.price),
						quantity: item.quantity
					}))
				}
			});

			console.log('[Tracking] purchase event sent:', orderData);
		} catch (error) {
			console.error('[Tracking] Error sending purchase event:', error);
		}

		this.fbPurchase(orderData);
	},

	/**
	 * Track product search
	 * @param {string} searchTerm - Search query
	 */
	search(searchTerm) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'search',
				search_term: searchTerm
			});

			console.log('[Tracking] search event sent:', searchTerm);
		} catch (error) {
			console.error('[Tracking] Error sending search event:', error);
		}

		if (this.isFacebookReady()) {
			try {
				window.fbq('track', 'Search', {
					search_string: searchTerm,
					content_category: 'Products'
				});
			} catch (error) {
				console.error('[Tracking] Error sending FB search event:', error);
			}
		}
	},

	/**
	 * Track when user views a category
	 * @param {Object} category - Category object
	 */
	viewCategory(category) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'view_item_list',
				ecommerce: {
					item_list_name: category.name || 'Category',
					item_list_id: category.id || 'category',
					items: []
				}
			});

			console.log('[Tracking] view_category event sent:', category);
		} catch (error) {
			console.error('[Tracking] Error sending view_category event:', error);
		}
	},

	/**
	 * Track wishlist addition
	 * @param {Object} product - Product object
	 */
	addToWishlist(product) {
		if (!this.isGTMReady()) {
			console.warn('[Tracking] GTM not ready');
			return;
		}

		try {
			window.dataLayer.push({
				event: 'add_to_wishlist',
				ecommerce: {
					currency: product.currency || 'USD',
					value: parseFloat(product.price),
					items: [{
						item_id: product.sku || String(product.id),
						item_name: product.name,
						item_category: product.category || '',
						price: parseFloat(product.price),
						quantity: 1
					}]
				}
			});

			console.log('[Tracking] add_to_wishlist event sent:', product);
		} catch (error) {
			console.error('[Tracking] Error sending add_to_wishlist event:', error);
		}

		if (this.isFacebookReady()) {
			try {
				window.fbq('track', 'AddToWishlist', {
					content_name: product.name,
					content_category: product.category || '',
					content_ids: [product.sku || String(product.id)],
					content_type: 'product',
					value: parseFloat(product.price),
					currency: product.currency || 'USD'
				});
			} catch (error) {
				console.error('[Tracking] Error sending FB AddToWishlist event:', error);
			}
		}
	},

	/**
	 * Facebook Pixel Events
	 */

	/**
	 * Track content view for Facebook
	 * @param {Object} product - Product object
	 */
	fbViewContent(product) {
		if (!this.isFacebookReady()) {
			console.warn('[Tracking] Facebook Pixel not ready');
			return;
		}

		try {
			window.fbq('track', 'ViewContent', {
				content_name: product.name,
				content_category: product.category || '',
				content_ids: [product.sku || String(product.id)],
				content_type: 'product',
				value: parseFloat(product.price),
				currency: product.currency || 'USD'
			});

			console.log('[Tracking] FB ViewContent event sent:', product);
		} catch (error) {
			console.error('[Tracking] Error sending FB ViewContent event:', error);
		}
	},

	/**
	 * Track add to cart for Facebook
	 * @param {Object} product - Product object
	 * @param {number} quantity - Quantity added
	 */
	fbAddToCart(product, quantity = 1) {
		if (!this.isFacebookReady()) {
			console.warn('[Tracking] Facebook Pixel not ready');
			return;
		}

		try {
			window.fbq('track', 'AddToCart', {
				content_name: product.name,
				content_category: product.category || '',
				content_ids: [product.sku || String(product.id)],
				content_type: 'product',
				value: parseFloat(product.price) * quantity,
				currency: product.currency || 'USD'
			});

			console.log('[Tracking] FB AddToCart event sent:', { product, quantity });
		} catch (error) {
			console.error('[Tracking] Error sending FB AddToCart event:', error);
		}
	},

	/**
	 * Track checkout initiation for Facebook
	 * @param {Array} cartItems - Array of cart items
	 * @param {number} totalValue - Total cart value
	 */
	fbInitiateCheckout(cartItems, totalValue) {
		if (!this.isFacebookReady()) {
			console.warn('[Tracking] Facebook Pixel not ready');
			return;
		}

		try {
			window.fbq('track', 'InitiateCheckout', {
				content_ids: cartItems.map(item => item.sku || String(item.id)),
				content_type: 'product',
				value: totalValue,
				currency: 'USD',
				num_items: cartItems.reduce((sum, item) => sum + item.quantity, 0)
			});

			console.log('[Tracking] FB InitiateCheckout event sent:', { totalValue, itemCount: cartItems.length });
		} catch (error) {
			console.error('[Tracking] Error sending FB InitiateCheckout event:', error);
		}
	},

	/**
	 * Track purchase for Facebook
	 * @param {Object} orderData - Order data object
	 */
	fbPurchase(orderData) {
		if (!this.isFacebookReady()) {
			console.warn('[Tracking] Facebook Pixel not ready');
			return;
		}

		try {
			window.fbq('track', 'Purchase', {
				content_ids: orderData.items.map(item => item.sku || String(item.id)),
				content_type: 'product',
				value: parseFloat(orderData.total),
				currency: orderData.currency || 'USD',
				num_items: orderData.items.length
			});

			console.log('[Tracking] FB Purchase event sent:', orderData);
		} catch (error) {
			console.error('[Tracking] Error sending FB Purchase event:', error);
		}
	},

	/**
	 * Campaign/Event Tracking
	 */

	/**
	 * Track campaign view
	 * @param {Object} campaign - Campaign object
	 */
	viewCampaign(campaign) {
		if (this.isGTMReady()) {
			try {
				window.dataLayer.push({
					event: 'view_campaign',
					campaign: {
						id: campaign.id,
						name: campaign.name,
						type: campaign.type || 'default'
					}
				});

				console.log('[Tracking] view_campaign event sent:', campaign);
			} catch (error) {
				console.error('[Tracking] Error sending view_campaign event:', error);
			}
		}

		if (this.isFacebookReady()) {
			try {
				window.fbq('trackCustom', 'ViewCampaign', {
					campaign_name: campaign.name,
					campaign_id: campaign.id,
					campaign_type: campaign.type || 'default'
				});

				console.log('[Tracking] FB ViewCampaign event sent:', campaign);
			} catch (error) {
				console.error('[Tracking] Error sending FB ViewCampaign event:', error);
			}
		}
	},

	/**
	 * Track campaign participation (join/opt-in)
	 * @param {Object} campaign - Campaign object
	 */
	joinCampaign(campaign) {
		if (this.isGTMReady()) {
			try {
				window.dataLayer.push({
					event: 'join_campaign',
					campaign: {
						id: campaign.id,
						name: campaign.name,
						type: campaign.type || 'default'
					}
				});

				console.log('[Tracking] join_campaign event sent:', campaign);
			} catch (error) {
				console.error('[Tracking] Error sending join_campaign event:', error);
			}
		}

		if (this.isFacebookReady()) {
			try {
				window.fbq('trackCustom', 'JoinCampaign', {
					campaign_name: campaign.name,
					campaign_id: campaign.id,
					campaign_type: campaign.type || 'default'
				});

				console.log('[Tracking] FB JoinCampaign event sent:', campaign);
			} catch (error) {
				console.error('[Tracking] Error sending FB JoinCampaign event:', error);
			}
		}
	},

	/**
	 * Page View Tracking
	 */

	/**
	 * Track page view
	 * @param {string} pageName - Page name/title
	 * @param {string} pageCategory - Page category
	 */
	pageView(pageName, pageCategory = '') {
		if (this.isGTMReady()) {
			try {
				window.dataLayer.push({
					event: 'page_view',
					page_title: pageName,
					page_category: pageCategory
				});

				console.log('[Tracking] page_view event sent:', { pageName, pageCategory });
			} catch (error) {
				console.error('[Tracking] Error sending page_view event:', error);
			}
		}
	},

	/**
	 * Track custom event
	 * @param {string} eventName - Event name
	 * @param {Object} eventData - Event data
	 */
	customEvent(eventName, eventData = {}) {
		if (this.isGTMReady()) {
			try {
				window.dataLayer.push({
					event: eventName,
					...eventData
				});

				console.log('[Tracking] Custom event sent:', { eventName, eventData });
			} catch (error) {
				console.error('[Tracking] Error sending custom event:', error);
			}
		}
	}
};

export default Tracking;
