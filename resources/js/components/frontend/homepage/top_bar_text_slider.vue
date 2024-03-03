<template>
    <div class="container">
      <transition name="slide-fade">
        <div v-if="currentItem !== null" class="text-center">
          {{ currentItem }}
        </div>
        <div v-else class="text-center">
          Loading...    
        </div>
      </transition>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        items: ['Text 1', 'Text 2', 'Text 3'],
        currentIndex: 0,
        currentItem: null
      };
    },
    mounted() {
      this.changeText();
    },
    methods: {
      changeText() {
        setTimeout(() => {
          this.currentItem = this.items[this.currentIndex];
          setTimeout(() => {
            this.currentIndex = (this.currentIndex + 1) % this.items.length;
            this.changeText();
          }, 2000); // Pause for 2 seconds before changing to the next item
        }, 4000); // Change slide every 4 seconds
      }
    }
  };
  </script>
  
  <style scoped>
  .slide-fade-enter-active, .slide-fade-leave-active {
    transition: all 1s ease;
  }
  .slide-fade-enter, .slide-fade-leave-to {
    transform: translateX(100%);
    opacity: 0;
  }
  .slide-fade-leave-active.slide-fade-leave-to {
    transform: translateX(-100%);
  }
  </style>
  