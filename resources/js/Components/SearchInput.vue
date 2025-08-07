<template>
  <div class="mb-2">
    <input
      type="text"
      v-model="searchTerm"
      @input="onSearch"
      class="w-full sm:w-1/3 px-4 py-2 border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-indigo-400"
      :placeholder="placeholder || 'Cari...'"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const emit = defineEmits(['update:search']);
const props = defineProps({
  modelValue: String,
  placeholder: String, // 👈 Tambahkan ini
});

const searchTerm = ref(props.modelValue || '');

watch(() => props.modelValue, val => {
  if (val !== searchTerm.value) searchTerm.value = val;
});

const onSearch = () => {
  emit('update:search', searchTerm.value);
};
</script>
