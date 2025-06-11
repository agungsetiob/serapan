<script setup>
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    parentOptions: Array,
    modelValue: [String, Number],
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <InputLabel for="parent_id" value="Parent Nota Dinas" />
        <SelectInput
            id="parent_id"
            :options="parentOptions"
            :value="modelValue"
            @update:value="emit('update:modelValue', $event)"
            class="mt-1 block w-full"
            required
        />
        <InputError message="Harap pilih parent nota dinas jika diperlukan" class="mt-2" />
    </div>
</template>
