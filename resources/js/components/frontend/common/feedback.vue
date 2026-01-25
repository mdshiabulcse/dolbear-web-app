<template lang="">
    <div v-if="messages && messages.length" class="container mb-5">
        <div class="card_new">
            <h2 class="card-title_new">STRAIGHT FROM OUR FANS</h2>

            <div class="feedback-slider-wrapper">
                <!-- Previous Button -->
                <button class="slider-arrow prev-btn" @click="prevSlide" :disabled="currentIndex === 0">
                    <i class="bx bx-chevron-left"></i>
                </button>

                <!-- Slider Container -->
                <div class="feedback-slider-container">
                    <div class="feedback-slider" :style="{ transform: `translateX(-${currentIndex * (cardWidth + gap)}px)` }">
                        <!-- Feedback Cards Loop -->
                        <div class="feedback-card d-flex flex-column justify-content-center"
                             v-for="(message, index) in messages"
                             :key="index"
                             :style="{ width: cardWidth + 'px', 'margin-right': gap + 'px', flexShrink: '0' }">

                            <!-- ============================================ -->
                            <!-- RATING STARS - SIMPLE SOLUTION -->
                            <!-- ============================================ -->
                            <div class="rating-stars">
                                <!-- Loop 5 times for 5 stars -->
                                <!-- star will be 1, 2, 3, 4, 5 -->
                                <!-- getStarClass returns 'bxs-star' or 'bx-star' -->
                                <i v-for="star in 5"
                                   :key="star"
                                   class="bx"
                                   :class="getStarClass(message.rating, star)"></i>
                            </div>

                            <!-- Customer Name -->
                            <h4>{{ message.name }}</h4>

                            <!-- Review Description -->
                            <p style="color: #212529;">{{ message.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Next Button -->
                <button class="slider-arrow next-btn" @click="nextSlide" :disabled="currentIndex >= messages.length - visibleCards">
                    <i class="bx bx-chevron-right"></i>
                </button>
            </div>

            <!-- Slider Dots Navigation -->
            <div class="slider-dots" v-if="messages.length > visibleCards">
                <span v-for="(dot, index) in messages.length - visibleCards + 1"
                      :key="index"
                      :class="{ active: currentIndex === index }"
                      @click="goToSlide(index)"></span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            messages: [],           // Store all flash messages
            currentIndex: 0,        // Current slide position
            cardWidth: 355,          // Width of each card
            gap: 20,                 // Gap between cards
            visibleCards: 3          // How many cards to show at once
        }
    },

    // When component is created
    async created() {
        await this.loadAllData();           // Load messages from API
        this.updateVisibleCards();           // Calculate responsive settings
        window.addEventListener('resize', this.updateVisibleCards);  // Update on resize
    },

    // Clean up event listener
    beforeUnmount() {
        window.removeEventListener('resize', this.updateVisibleCards);
    },

    methods: {
        // ============================================
        // LOAD FLASH MESSAGES FROM API
        // ============================================
        async loadAllData() {
            const baseUrl = `${window.location.protocol}//${window.location.host}`;
            const url = `${baseUrl}/flash-message/all`;

            axios.get(url).then((response) => {
                if (response.data.error) {
                    toastr.error(response.data.error, this.lang.Error + " !!");
                } else {
                    this.messages = response.data.data;

                    // DEBUG: Check what we got
                    console.log('=== DEBUG INFO ===');
                    console.log('Messages loaded:', this.messages);
                    console.log('First message:', this.messages[0]);
                    console.log('Rating value:', this.messages[0]?.rating);
                    console.log('Rating type:', typeof this.messages[0]?.rating);
                    console.log('Rating as number:', Number(this.messages[0]?.rating));
                }
            }).catch((error) => {
                console.error('Error loading messages:', error);
            });
        },

        // ============================================
        // GET STAR CLASS USING IF/ELSE CONDITIONS
        // ============================================
        getStarClass(rating, starNumber) {
            // Convert rating to integer
            let ratingValue = parseInt(rating);

            // If rating is NaN, use default 5
            if (isNaN(ratingValue)) {
                ratingValue = 5;
            }

            // Check if current star should be filled or empty
            if (starNumber <= ratingValue) {
                return 'bxs-star';
            } else {
                return 'bx-star';
            }
        },

        // ============================================
        // UPDATE RESPONSIVE SETTINGS
        // ============================================
        updateVisibleCards() {
            if (window.innerWidth < 768) {
                // Mobile: Show 1 card
                this.visibleCards = 1;
                this.cardWidth = Math.min(window.innerWidth - 90, 355);
            } else if (window.innerWidth < 992) {
                // Tablet: Show 2 cards
                this.visibleCards = 2;
                this.cardWidth = Math.min((window.innerWidth - 110) / 2, 355);
            } else {
                // Desktop: Show 3 cards
                this.visibleCards = 3;
                this.cardWidth = 355;
            }
        },

        // ============================================
        // SLIDER NAVIGATION
        // ============================================
        nextSlide() {
            if (this.currentIndex < this.messages.length - this.visibleCards) {
                this.currentIndex++;
            }
        },

        prevSlide() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },

        goToSlide(index) {
            this.currentIndex = index;
        }
    }
}
</script>

<style scoped>
/* ============================================ */
/* SECTION STYLES */
/* ============================================ */
.card_new {
    padding: 40px 20px;
}

.card-title_new {
    text-align: center;
    font-size: 32px;
    font-weight: bold;
    color: #0B0B0B;
    margin-bottom: 40px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ============================================ */
/* SLIDER WRAPPER */
/* ============================================ */
.feedback-slider-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

.feedback-slider-container {
    flex: 1;
    overflow: hidden;
    padding: 10px 0;
}

.feedback-slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

/* ============================================ */
/* FEEDBACK CARD */
/* ============================================ */
.feedback-card {
    height: 182px;
    border-radius: 10px;
    border: 1px solid #707070;
    padding: 10px 20px;
    box-shadow: 9px 5px 5.5px 0px #00000017;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    background: white;
}

/* ============================================ */
/* RATING STARS */
/* ============================================ */
.rating-stars {
    display: flex;
    justify-content: center;
    gap: 3px;
    margin-bottom: 10px;
}

.rating-stars .bx {
    font-size: 22px;
}

/* Filled star - Gold color */
.rating-stars .bxs-star {
    color: #FFD700;
}

/* Empty star - Gray color */
.rating-stars .bx-star {
    color: #d0d0d0;
}

/* ============================================ */
/* CARD TEXT */
/* ============================================ */
.feedback-card h4 {
    font-size: 16px;
    line-height: 27px;
    margin-bottom: 10px;
    font-weight: bold;
    color: #0B0B0B;
}

.feedback-card p {
    font-size: 16px;
    line-height: 19px;
    font-weight: bold;
    color: #212529;
}

/* ============================================ */
/* SLIDER ARROWS */
/* ============================================ */
.slider-arrow {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #168FC3 0%, #57D9FF 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    z-index: 10;
}

.slider-arrow:hover:not(:disabled) {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(22, 143, 195, 0.4);
}

.slider-arrow:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* ============================================ */
/* SLIDER DOTS */
/* ============================================ */
.slider-dots {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.slider-dots span {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e0e0e0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.slider-dots span.active {
    background: linear-gradient(135deg, #168FC3 0%, #57D9FF 100%);
    width: 30px;
    border-radius: 6px;
}

/* ============================================ */
/* RESPONSIVE - TABLET */
/* ============================================ */
@media screen and (max-width: 992px) {
    .feedback-slider-wrapper {
        gap: 10px;
    }

    .slider-arrow {
        width: 35px;
        height: 35px;
        font-size: 20px;
    }

    .feedback-card {
        height: 200px;
    }
}

/* ============================================ */
/* RESPONSIVE - MOBILE */
/* ============================================ */
@media screen and (max-width: 768px) {
    .card_new {
        padding: 30px 15px;
    }

    .card-title_new {
        font-size: 24px;
        margin-bottom: 30px;
    }

    .feedback-slider-wrapper {
        gap: 8px;
    }

    .slider-arrow {
        width: 32px;
        height: 32px;
        font-size: 18px;
    }

    .slider-dots {
        gap: 8px;
        margin-top: 20px;
    }

    .slider-dots span {
        width: 10px;
        height: 10px;
    }

    .slider-dots span.active {
        width: 24px;
    }

    .feedback-card {
        height: auto;
        min-height: 200px;
    }

    .rating-stars .bx {
        font-size: 20px;
    }
}

/* ============================================ */
/* RESPONSIVE - SMALL MOBILE */
/* ============================================ */
@media screen and (max-width: 480px) {
    .card_new {
        padding: 20px 10px;
    }

    .card-title_new {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .feedback-slider-wrapper {
        gap: 5px;
    }

    .slider-arrow {
        width: 30px;
        height: 30px;
        font-size: 16px;
    }

    .feedback-card {
        padding: 12px 15px;
    }

    .rating-stars .bx {
        font-size: 18px;
    }

    .feedback-card h4 {
        font-size: 14px;
    }

    .feedback-card p {
        font-size: 14px;
    }
}
</style>
