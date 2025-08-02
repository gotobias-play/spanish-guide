<template>
  <transition name="modal-fade">
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-20 p-4" @click.self="$emit('close')">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto transform transition-all duration-300 ease-out" ref="modalContent">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
          <h2 class="text-2xl font-bold text-gray-800">{{ title }}</h2>
          <button @click="$emit('close')" class="text-2xl font-bold text-gray-500 hover:text-gray-800 transition-colors">&times;</button>
        </div>
        <div>
          <slot></slot>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'Modal',
  props: {
    title: {
      type: String,
      required: true,
    },
  },
  mounted() {
    gsap.from(this.$refs.modalContent, { duration: 0.3, scale: 0.9, opacity: 0, ease: 'power2.out' });
  },
};
</script>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s;
}
.modal-fade-enter, .modal-fade-leave-to {
  opacity: 0;
}
</style>
