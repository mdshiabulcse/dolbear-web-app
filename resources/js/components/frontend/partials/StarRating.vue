<template>
  <div class="star-rating" :style="{ fontSize: starSize + 'px' }">
    <span
      v-for="star in 5"
      :key="star"
      @click="setRating(star)"
      :style="{
        cursor: readOnly ? 'default' : 'pointer',
        color: star <= currentRating ? activeColor : '#ccc',
        marginRight: '2px'
      }"
    >★</span>
  </div>
</template>

<script>
export default {
  name: 'StarRating',
  props: {
    rating: { type: Number, default: 0 },
    readOnly: { type: Boolean, default: false },
    starSize: { type: Number, default: 20 },
    activeColor: { type: String, default: '#C9151B' },
    roundStartRating: { type: Boolean, default: false },
  },
  computed: {
    currentRating() {
      return this.roundStartRating ? Math.round(this.rating) : this.rating;
    }
  },
  methods: {
    setRating(star) {
      if (!this.readOnly) {
        this.$emit('update:rating', star);
      }
    }
  }
};
</script>