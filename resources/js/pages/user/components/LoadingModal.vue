<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-red-800 p-8 rounded-lg max-w-md w-full mx-4">
      <p class="text-xl font-bold text-white mb-4">Loading {{ title }}...</p>

      <div class="relative w-full">
        <!-- Progress bar -->
        <div class="w-full bg-gray-700 rounded h-4 overflow-hidden">
          <div class="bg-blue-500 h-full transition-all duration-200" :style="{ width: internalProgress + '%' }"></div>
        </div>

        <!-- Sparkle following the progress tip -->
        <img 
          :src="randomSparkle" 
          class="absolute -top-8 w-20 h-20 pointer-events-none opacity-90 transform rotate-12 transition-all duration-200" 
          :style="{ left: `calc(${internalProgress}% - 2rem)` }"
        >
      </div>

      <p class="text-sm mt-3 text-gray-300">{{ internalProgress }}%</p>
    </div>
  </div>
</template>

<script>
export default {
  props: { isOpen: Boolean, title: String},
  data() {
    return {
      internalProgress: 0,
      progressInterval: null,
      randomSparkle: '',
    }
  },
  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.internalProgress = 0;
        this.pickRandomSparkle();
        this.startProgress();
      } else {
        this.stopProgress();
      }
    }
  },
  methods: {
    startProgress() {
      this.progressInterval = setInterval(() => {
        if (this.internalProgress < 95) {
          this.internalProgress = Math.min(95, Math.floor(this.internalProgress + Math.random() * 10));
        }
      }, 200);
    },
    stopProgress() {
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
        this.progressInterval = null;
      }
    },
    pickRandomSparkle() {
      const stickerNum = Math.floor(Math.random() * 6) + 1;
      this.randomSparkle = `/images/stickers/sparkle0${stickerNum}.webp`;
    }
  },
  beforeUnmount() {
    this.stopProgress();
  }
}
</script>
