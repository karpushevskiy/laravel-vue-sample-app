<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ContentLoader from '@/Components/ContentLoader.vue';
import DashboardStatusAndErrors from '@/Components/DashboardStatusAndErrors.vue';

const props = defineProps({
    errors: Object,
    status: String,
    showLoader: Boolean,
});

const currentUser = usePage().props.auth.user ?? {};

const topBar = ref(null);

const menuItemsList = ref({});
const menuGroupsList = ref({});


/*
 * Computed
 */
const errors = computed(() => props.errors ?? usePage().props.errors);
const status = computed(() => props.status ?? usePage().props.status);


/*
 * Lifecycle hooks
 */
onMounted(() => {
    if (currentUser.is_super_admin || currentUser.is_admin) {
        menuItemsList.value['users.index'] = 'Users';
        menuGroupsList.value['users.index'] = 'users.*';
    }

    menuItemsList.value['profile.edit'] = 'Profile';
    menuGroupsList.value['profile.edit'] = 'profile.*';
});
</script>

<template>
    <v-responsive>
        <v-layout class="rounded rounded-md">
            <v-navigation-drawer v-model="topBar">
                <v-list>
                    <Link :href="route('main.index')">
                        <ApplicationLogo class="tw-h-24 tw-px-6 tw-my-5"/>
                    </Link>

                    <template v-for="(title, name) in menuItemsList">
                        <Link :href="route(name)" :active="route().current(name)">
                            <v-list-item :to="route(name)" :active="route().current(menuGroupsList[name])" :title="title"></v-list-item>
                        </Link>
                    </template>
                </v-list>


                <!--Bottom sidebar side-->
                <template v-slot:append>
                    <div class="pa-2">
                        <Link :href="route('auth.logout')" method="post">
                            <v-btn block>Log Out</v-btn>
                        </Link>
                    </div>
                </template>
            </v-navigation-drawer>

            <v-app-bar>
                <div class="tw-max-w-full tw-py-6 tw-px-4 sm:tw-px-6 lg:tw-px-8">
                    {{ $page.props.auth.user.full_name }}
                    {{ $page.props.auth.user.last_seen_at }}
                </div>

                <template v-slot:append>
                    <v-app-bar-nav-icon class="hidden-lg-and-up" @click.stop="topBar = !topBar"></v-app-bar-nav-icon>
                </template>
            </v-app-bar>

            <v-main style="min-height: 100vh;">
                <div class="tw-relative h-100">
                    <!-- Page Heading -->
                    <header class="tw-bg-white tw-shadow" v-if="$slots.header">
                        <div class="tw-flex tw-items-center tw-justify-between tw-max-w-full tw-mx-auto tw-py-6 tw-px-4 sm:tw-px-6 lg:tw-px-8">
                            <slot name="header" />
                        </div>
                    </header>

                    <DashboardStatusAndErrors :errors="errors"
                                              :status="status"/>
                    <slot />

                    <v-fade-transition>
                        <ContentLoader v-if="showLoader"/>
                    </v-fade-transition>
                </div>
            </v-main>
        </v-layout>
    </v-responsive>
</template>
