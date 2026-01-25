<template>
  <div class="sg-page-content">
    <section class="coming-soon-section">
      <div class="container">
        <div class="coming-soon-wrapper">
          <div class="coming-soon-content">
            <div class="gift-icon">
              <img :src="`${baseUrl}/images/img/icon/gift.png`" alt="Gift Icon">
            </div>
            <h1 class="coming-soon-title">Coming Soon</h1>
            <p class="coming-soon-subtitle">Exciting Offers on the Way!</p>
            <p class="coming-soon-description">
              We're preparing something special for you. Stay tuned for amazing deals, discounts, and exclusive offers that will make your shopping experience even better.
            </p>
            <div class="coming-soon-features">
              <div class="feature-item">
                <div class="feature-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                  </svg>
                </div>
                <span>Exclusive Deals</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                  </svg>
                </div>
                <span>Special Discounts</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                  </svg>
                </div>
                <span>Limited Time Offers</span>
              </div>
            </div>

            <!-- Subscriber Form -->
            <div class="subscribe-section">
              <p class="subscribe-title">Subscribe for Exclusive Offers</p>
              <p class="subscribe-text">Get notified when we launch and receive special deals!</p>
              <form @submit.prevent="subscribe" class="subscribe-form">
                <div class="input-group">
                  <input
                    v-model="form.email"
                    type="email"
                    class="subscribe-input"
                    placeholder="Enter your email address"
                    required
                  />
                  <button type="submit" class="subscribe-btn" :disabled="isSubmitting">
                    <span v-if="!isSubmitting">Subscribe</span>
                    <span v-else>
                      <svg class="spinner" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.3"></circle>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                      </svg>
                    </span>
                  </button>
                </div>
                <p v-if="subscribeMessage" class="subscribe-message" :class="subscribeMessageType">
                  {{ subscribeMessage }}
                </p>
              </form>
            </div>

            <div class="notify-section">
              <router-link :to="{ name: 'home' }" class="btn-back-home">
                Continue Shopping
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: "offers",
  data() {
    return {
      baseUrl: "",
      form: {
        email: "",
      },
      isSubmitting: false,
      subscribeMessage: "",
      subscribeMessageType: "",
    };
  },
  mounted() {
    this.baseUrl = `${window.location.protocol}//${window.location.host}`;
  },
  methods: {
    subscribe() {
      this.isSubmitting = true;
      this.subscribeMessage = "";

      let url = this.getUrl("home/subscribers");

      axios
        .post(url, this.form)
        .then((response) => {
          if (response.data.success) {
            this.subscribeMessage = response.data.success || "Thank you for subscribing!";
            this.subscribeMessageType = "success";
            this.form.email = "";
          } else {
            if (response.data.error) {
              this.subscribeMessage = response.data.error;
              this.subscribeMessageType = "error";
            }
          }
        })
        .catch((error) => {
          if (error.response && error.response.status == 422) {
            let errors = Object.keys(error.response.data.errors);
            this.subscribeMessage = error.response.data.errors[errors[0]][0];
            this.subscribeMessageType = "error";
          } else {
            this.subscribeMessage = "Something went wrong. Please try again.";
            this.subscribeMessageType = "error";
          }
        })
        .finally(() => {
          this.isSubmitting = false;
          setTimeout(() => {
            this.subscribeMessage = "";
          }, 5000);
        });
    },
    getUrl(url) {
      return `${window.location.origin}/${url}`;
    },
  },
};
</script>

<style scoped>
.coming-soon-section {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.coming-soon-wrapper {
  width: 100%;
  max-width: 800px;
}

.coming-soon-content {
  text-align: center;
  background: white;
  padding: 60px 40px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.gift-icon {
  margin-bottom: 30px;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.gift-icon img {
  width: 120px;
  height: 120px;
  object-fit: contain;
}

.coming-soon-title {
  font-size: 48px;
  font-weight: 700;
  color: #0B0B0B;
  margin-bottom: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.coming-soon-subtitle {
  font-size: 24px;
  font-weight: 500;
  color: #168FC3;
  margin-bottom: 30px;
}

.coming-soon-description {
  font-size: 16px;
  color: #666;
  line-height: 1.8;
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.coming-soon-features {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-bottom: 40px;
  flex-wrap: wrap;
}

.feature-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 20px;
  min-width: 150px;
}

.feature-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #168FC3 0%, #57D9FF 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-item:hover .feature-icon {
  transform: scale(1.1);
  box-shadow: 0 5px 20px rgba(22, 143, 195, 0.4);
}

.feature-item span {
  font-size: 14px;
  font-weight: 600;
  color: #0B0B0B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.subscribe-section {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 30px;
  border-radius: 15px;
  margin: 30px 0;
}

.subscribe-title {
  font-size: 20px;
  font-weight: 600;
  color: #0B0B0B;
  margin-bottom: 10px;
}

.subscribe-text {
  font-size: 14px;
  color: #666;
  margin-bottom: 20px;
}

.subscribe-form .input-group {
  display: flex;
  gap: 10px;
  max-width: 500px;
  margin: 0 auto;
}

.subscribe-input {
  flex: 1;
  padding: 14px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 30px;
  font-size: 14px;
  transition: all 0.3s ease;
  outline: none;
}

.subscribe-input:focus {
  border-color: #168FC3;
  box-shadow: 0 0 0 3px rgba(22, 143, 195, 0.1);
}

.subscribe-btn {
  padding: 14px 30px;
  background: linear-gradient(135deg, #168FC3 0%, #57D9FF 100%);
  color: white;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  min-width: 120px;
}

.subscribe-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(22, 143, 195, 0.3);
}

.subscribe-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.subscribe-message {
  margin-top: 15px;
  font-size: 14px;
  padding: 10px 15px;
  border-radius: 8px;
  text-align: center;
}

.subscribe-message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.subscribe-message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.notify-section {
  border-top: 1px solid #eee;
  padding-top: 30px;
}

.notify-text {
  font-size: 16px;
  color: #666;
  margin-bottom: 20px;
}

.btn-back-home {
  display: inline-block;
  padding: 14px 40px;
  background: linear-gradient(135deg, #168FC3 0%, #57D9FF 100%);
  color: white;
  text-decoration: none;
  border-radius: 30px;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(22, 143, 195, 0.3);
}

.btn-back-home:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(22, 143, 195, 0.4);
  color: white;
}

/* Tablet styles */
@media (max-width: 768px) {
  .coming-soon-content {
    padding: 40px 30px;
  }

  .gift-icon img {
    width: 100px;
    height: 100px;
  }

  .coming-soon-title {
    font-size: 36px;
  }

  .coming-soon-subtitle {
    font-size: 20px;
  }

  .coming-soon-description {
    font-size: 14px;
  }

  .coming-soon-features {
    gap: 20px;
  }

  .feature-item {
    min-width: 120px;
    padding: 15px;
  }

  .feature-icon {
    width: 50px;
    height: 50px;
  }

  .feature-item span {
    font-size: 12px;
  }

  .subscribe-section {
    padding: 25px 20px;
  }

  .subscribe-title {
    font-size: 18px;
  }

  .subscribe-form .input-group {
    flex-direction: column;
    max-width: 100%;
  }

  .subscribe-btn {
    width: 100%;
  }
}

/* Mobile styles */
@media (max-width: 480px) {
  .coming-soon-section {
    padding: 40px 15px;
    min-height: calc(100vh - 150px);
  }

  .coming-soon-content {
    padding: 30px 20px;
  }

  .gift-icon img {
    width: 80px;
    height: 80px;
  }

  .coming-soon-title {
    font-size: 28px;
    letter-spacing: 1px;
  }

  .coming-soon-subtitle {
    font-size: 18px;
  }

  .coming-soon-description {
    font-size: 14px;
    margin-bottom: 30px;
  }

  .coming-soon-features {
    gap: 15px;
  }

  .feature-item {
    min-width: 100px;
    padding: 10px;
  }

  .feature-icon {
    width: 45px;
    height: 45px;
  }

  .feature-icon svg {
    width: 20px;
    height: 20px;
  }

  .feature-item span {
    font-size: 11px;
  }

  .notify-text {
    font-size: 14px;
  }

  .btn-back-home {
    padding: 12px 30px;
    font-size: 14px;
  }
}

/* Extra small devices */
@media (max-width: 360px) {
  .coming-soon-title {
    font-size: 24px;
  }

  .coming-soon-subtitle {
    font-size: 16px;
  }

  .coming-soon-features {
    flex-direction: column;
    align-items: center;
  }

  .feature-item {
    flex-direction: row;
    gap: 15px;
    min-width: auto;
    width: 100%;
    max-width: 250px;
  }
}
</style>
