<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password"/>

        <div class="tw-mb-4 tw-text-sm tw-text-gray-600">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <div>
                <v-text-field type="password"
                              autocomplete="current-password"
                              v-model="form.password"
                              :error-messages="form.errors.password"
                              label="Password"
                              required></v-text-field>
            </div>

            <div class="tw-flex tw-justify-end tw-mt-4">
                <v-btn-primary type="submit">Confirm</v-btn-primary>
            </div>
        </form>
    </GuestLayout>
</template>
