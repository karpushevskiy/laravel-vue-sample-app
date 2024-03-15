<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { computed, ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    errors: Object,
    status: String,
});

// TODO: Перенести showState внутрь computed (если они меняются - showState = true)
const hasErrors = computed(() => Object.keys(props.errors).length > 0);
const hasStatus = computed(() => props.status !== undefined && props.status !== "" && props.status !== null && props.status !== false);

const showState = ref(false);

/*
 * Inertia events handling
 */
// For page reloading on pagination or searching
Inertia.on('success', (event) => {
    const newProps = event.detail.page.props;

    showState.value = newProps && ((newProps.errors && Object.keys(newProps.errors).length > 0) || (newProps.status && newProps.status !== undefined && newProps.status !== "" && newProps.status !== null && newProps.status !== false));
});

/*
 * Methods
 */
const hide = () => {
    showState.value = false;
};
</script>

<template>
    <div v-if="showState" class="tw-flex tw-items-center tw-justify-between tw-max-w-full tw-mx-auto tw-py-6 tw-px-4 sm:tw-px-6 lg:tw-px-8">
        <div v-if="hasErrors">
            <div class="tw-font-medium tw-text-red-600">Whoops! Something went wrong.</div>

            <ul class="tw-mt-3 tw-list-disc tw-list-inside tw-text-sm tw-text-red-600">
                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
            </ul>
        </div>

        <div v-if="hasStatus">
            <div class="tw-font-medium tw-text-green-600">Success! {{ status }}</div>
        </div>

        <v-hover v-slot:default="{ isHovering, props }">
            <v-btn v-bind="props" :color="isHovering ? 'accent' : 'primary'" dark @click="hide">
                Close
            </v-btn>
        </v-hover>
    </div>
</template>
