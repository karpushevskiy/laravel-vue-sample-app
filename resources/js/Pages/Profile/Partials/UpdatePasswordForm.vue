<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: (page) => {
          form.reset();
        },
        onError: (err) => {
          if (form.errors.password) {
            form.reset('password', 'password_confirmation');
            passwordInput.value.focus();
          }
          if (form.errors.current_password) {
            form.reset('current_password');
            currentPasswordInput.value.focus();
          }
        },
        onFinish: () => {
          form.processing = false;
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Update Password</h2>

            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="tw-mt-6 tw-space-y-6">
            <div>
                <v-text-field
                    type="password"
                    autocomplete="current-password"
                    v-model="form.current_password"
                    :error-messages="form.errors.current_password"
                    label="Current Password"
                    :disabled="form.processing"
                ></v-text-field>
            </div>

            <div>
                <v-text-field
                    type="password"
                    autocomplete="new-password"
                    v-model="form.password"
                    :error-messages="form.errors.password"
                    label="New Password"
                    :disabled="form.processing"
                ></v-text-field>
            </div>

            <div>
                <v-text-field
                    type="password"
                    autocomplete="new-password"
                    v-model="form.password_confirmation"
                    :error-messages="form.errors.password_confirmation"
                    label="Confirm Password"
                    :disabled="form.processing"
                ></v-text-field>
            </div>

            <div class="tw-flex tw-items-center tw-gap-4">
                <v-btn-primary type="submit" class="tw-ml-4" :disabled="form.processing">Save</v-btn-primary>

                <Transition enter-from-class="tw-opacity-0" leave-to-class="tw-opacity-0" class="tw-transition ease-in-out">
                    <p v-if="form.recentlySuccessful" class="tw-text-sm tw-text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
