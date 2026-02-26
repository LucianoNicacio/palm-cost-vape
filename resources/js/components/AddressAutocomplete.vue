<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

interface AddressComponents {
    address: string;
    city: string;
    state: string;
    zip_code: string;
}

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
    error?: string;
    allowedZips?: string[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'address-selected', components: AddressComponents): void;
    (e: 'zip-error', message: string): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const zipError = ref('');

onMounted(() => {
    initAutocomplete();
});

const initAutocomplete = () => {
    if (!inputRef.value) return;

    // Check if Google Maps is loaded
    if (typeof google === 'undefined' || !google.maps?.places) {
        // Retry after a short delay — the script might still be loading
        setTimeout(initAutocomplete, 500);
        return;
    }

    const autocomplete = new google.maps.places.Autocomplete(inputRef.value, {
        types: ['address'],
        componentRestrictions: { country: 'us' },
        fields: ['address_components', 'formatted_address'],
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.address_components) return;

        let streetNumber = '';
        let route = '';
        let city = '';
        let state = '';
        let zip = '';

        for (const component of place.address_components) {
            const type = component.types[0];
            switch (type) {
                case 'street_number':
                    streetNumber = component.long_name;
                    break;
                case 'route':
                    route = component.long_name;
                    break;
                case 'locality':
                    city = component.long_name;
                    break;
                case 'administrative_area_level_1':
                    state = component.short_name;
                    break;
                case 'postal_code':
                    zip = component.long_name;
                    break;
            }
        }

        const address = streetNumber ? `${streetNumber} ${route}` : route;

        emit('update:modelValue', address);
        emit('address-selected', {
            address,
            city,
            state,
            zip_code: zip,
        });

        // Check zip against allowed list
        if (props.allowedZips?.length && zip) {
            if (!props.allowedZips.includes(zip)) {
                zipError.value = 'We currently only serve Flagler County residents.';
                emit('zip-error', zipError.value);
            } else {
                zipError.value = '';
            }
        }
    });
};

const onInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <input
        ref="inputRef"
        :value="modelValue"
        @input="onInput"
        type="text"
        :placeholder="placeholder || 'Start typing your address...'"
        autocomplete="off"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
        :class="{ 'border-red-500': error || zipError }"
    />
    <p v-if="zipError" class="mt-1 text-sm text-red-500">
        {{ zipError }}
    </p>
</template>
