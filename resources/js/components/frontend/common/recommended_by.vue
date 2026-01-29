<template lang="">
  <div v-if="recommends && recommends.length" class="container mt-5 mb-5 "  >
      <div class="card_new ">
      <h2 class="card-title_new">RECOMMENDED BY</h2>
      <div class="row" v-if="recommends.length > 0">
          <div class="col-12 col-md-12 d-flex justify-content-start">
            <div class="recomeded-slider">
  <div
    class="recomended-item"
    :class="details.id === item.id && isDesktop ? 'item-active' : ''"
    v-for="(item, index) in recommends"
    :key="index"
  >
    <!-- Image Section -->
    <img
      class="recomended-section-image"
      v-if="isDesktop"
      @click="onShowDetails(item)"
      :src="item.image"
      alt=""
    />
    <img
      class="recomended-section-image"
      v-else
      :src="item.image"
      alt=""
    />
    <!-- Content Section -->
    <div class="item-detail">
      <h4>{{ item.name }}</h4>
      <!-- Show full description on mobile, truncated on desktop -->
      <p class = "recomended_content">
        {{ isDesktop
          ? item.description.slice(0, 40) + (item.description.length > 20 ? '...' : '')
          : item.description }}
      </p>
    </div>
  </div>
</div>


          </div>
      </div>
      </div>

  </div>
</template>
<script>
export default {
  name: "recommended_by",

  data() {
    return {
      recommends: [], // Recommendation list
      details: "", // Selected item details
      isDesktop: true, // Determines if the device is desktop
    };
  },

  async created() {
    this.detectScreenSize(); // Detect screen size on load
    window.addEventListener("resize", this.detectScreenSize); // Listen for screen resize
    await this.loadRecommends();
  },

  beforeUnmount() {
    window.removeEventListener("resize", this.detectScreenSize); // Clean up listener
  },

  methods: {
    detectScreenSize() {
      // Update isDesktop based on window width
      this.isDesktop = window.innerWidth > 768;
    },

    async loadRecommends() {
      const url = `${window.location.protocol}//${window.location.host}/recommended/all`;
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.recommends = response.data?.data.slice(-5); // Limit to 5 items
          this.details = this.recommends[0]; // Default selection
        }
      });
    },

    onShowDetails(item) {
      if (this.isDesktop) {
        this.details = item; // Update details only for desktop
      }
    },
  },
};
</script>

<style scoped>
.card-details p {
  font-size: 16px;
}
.card-details .comment {
  font-weight: 500;
}

/* Container for the slider */
.recomeded-slider {
  display: flex;
  gap: 5px;
  height: 200px;
}

/* Default item style */
.recomended-item {
  display: flex;
  width: 170px;
  transition: width 0.3s ease;
  overflow: hidden;
}

/* Active item for desktop */
.recomended-item.item-active {
  width: 500px;
}

/* Detail section inside each item */
.recomended-item .item-detail {
  margin-left: 20px;
}

/* Image styling */
.recomended-item img {
  transition: width 0.3s ease;
  width: 200px;
  height: 200px;
  object-fit: cover !important;
}

.recomended_content{
  font-size: 16px;
  color: #212529 !important;
}

/* Mobile Styles */
@media screen and (max-width: 768px) {
  .recomeded-slider {
    width: 100%;
    height: auto;
    display: flex;
    flex-direction: column;
  }

  .recomended-item {
    display: flex;
    flex-direction: row;
    width: 100%; /* Make items full width on mobile */
    height: auto;
    transition: none; /* No animation for mobile */
    overflow: visible; /* Ensure all content is visible */
  }

  .recomended-item.item-active {
    width: 100%;
  }

  .recomended-item img {
    width: 150px;
    height: 150px;
    object-fit: cover !important;
  }

  .recomended-item .item-detail {
    margin-left: 15px;
  }

  /* Ensure full details are displayed */ 
  .recomended-item .item-detail p {
    font-size: 14px;
    line-height: 1.5;
  }
}

/* Tablet Styles (Optional additional adjustments) */
@media screen and (max-width: 435px) {
  .recomended-item img {
    width: 150px;
    height: 150px;
  }

  .recomended-item {
    width: 100%;
  }
}
</style>
