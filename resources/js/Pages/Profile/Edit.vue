<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profile"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="tw-font-semibold tw-text-xl tw-text-gray-800 tw-leading-tight">Profile</h2>
        </template>

        <div class="tw-py-12">
            <div class="tw-max-w-full tw-mx-auto sm:tw-px-6 lg:tw-px-8 tw-space-y-6">
                <div class="p-4 sm:tw-p-8 tw-bg-white tw-shadow">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="tw-max-w-xl"
                    />
                </div>

                <div class="tw-p-4 sm:tw-p-8 tw-bg-white tw-shadow">
                    <UpdatePasswordForm class="tw-max-w-xl" />
                </div>

                <div class="tw-p-4 sm:tw-p-8 tw-bg-white tw-shadow">
                    <DeleteUserForm class="tw-max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
