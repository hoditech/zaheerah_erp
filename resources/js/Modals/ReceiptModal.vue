<template>
    <div v-if="isOpen" class="modal-overlay">
      <div class="modal-content">
        <button @click="closeModal" class="close-button">Close</button>
        <div v-html="receiptHtml"></div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const isOpen = ref(false);
  const receiptHtml = ref('');
  
  const openModal = (html) => {
    receiptHtml.value = html;
    isOpen.value = true;
  };
  
  const closeModal = () => {
    isOpen.value = false;
    receiptHtml.value = '';
  };
  
  defineExpose({ openModal });
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 80%;
    max-height: 80%;
    overflow-y: auto;
  }
  
  .close-button {
    margin-bottom: 10px;
  }
  </style>