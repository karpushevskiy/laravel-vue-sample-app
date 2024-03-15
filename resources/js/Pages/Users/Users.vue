<!--
  - GorKa Team
  - Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
  -->

<script setup>
import { computed, ref, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import mimeDb from 'mime-db';
import NProgress from 'nprogress';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    items: Array,
    currentPage: Number,
    perPage: Number,
    lastPage: Number,
    totalItems: Number,
    paging: Boolean,
    orderBy: String,
    sort: String,
    filters: Object,
    errors: Object,
    roles: Array,
});


/*
 * Inertia events handling
 */
// For page reloading on pagination or searching
Inertia.on('success', (event) => {
    const newProps = event.detail.page.props;
    const pageType = event.detail.page.component;

    if (pageType === 'Users/Users' && newProps) {
        const updatedRefs = {items, roles, currentPage, perPage, orderBy, sort, totalItems, lastPage, fullNameFilter, emailFilter};

        for (let refName in updatedRefs) {
            if (refName.toLowerCase().includes('filter')) {
                let filterRefName = refName.replace(/([A-Z])/g, '_$1').toLowerCase().replace('filter', '').replace(/^_+|_+$/g, '');
                updatedRefs[refName].value = newProps.additionalData ? newProps.additionalData?.filters[filterRefName] : newProps?.filters[filterRefName];
            } else {
                if ((newProps.additionalData && newProps.additionalData[refName]) || newProps[refName]) {
                    updatedRefs[refName].value = newProps.additionalData ? newProps.additionalData[refName] : newProps[refName];
                }
            }
        }
    }
});


/*
 * Main data
 */
const currentUser = usePage().props.auth.user ?? {};

const items = ref(props.items);
const roles = ref(props.roles);
const currentPage = ref(props.currentPage ?? 1);
const perPage = ref(props.perPage ? props.perPage : 10);
const lastPage = ref(props.lastPage ?? 1);
const totalItems = ref(props.totalItems ?? 0);
const orderBy = ref(props.orderBy);
const sort = ref(props.sort);
const fullNameFilter = ref(props.filters?.full_name);
const emailFilter = ref(props.filters?.email);
const search = ref('');
const errors = ref(props.errors || {});

const loading = ref(true);
const dialog = ref(false);
const dialogDeleteItem = ref(false);
const fileInProgress = ref(false);
const editedIndex = ref(-1);

const tableHeaders = [
    { title: 'First Name', key: 'first_name', width: '20%', sortable: true },
    { title: 'Last Name', key: 'last_name', width: '20%', sortable: true, align: 'start' },
    { title: 'Email', key: 'email', width: '20%', sortable: true },
    { title: 'Role', key: 'is_client', width: '20%', sortable: true },
    { title: 'Actions', key: 'actions', width: '10%', sortable: false },
];

const tablePerPageOptions = [
    {value: 10, title: '10'},
    {value: 25, title: '25'},
    {value: 50, title: '50'},
    {value: 100, title: '100'},
    {value: -1, title: '$vuetify.dataFooter.itemsPerPageAll'}
];


/*
 * Computed
 */
const modifiedItems = computed(() => {
    return items.value ? items.value.map(item => {
        const modifiedItem = { ...item };
        for (const key in modifiedItem) {
            if (modifiedItem[key] === null || modifiedItem[key] === undefined) {
                modifiedItem[key] = '-';
            }
        }
        return modifiedItem;
    }) : [];
});

const allFiltersEmpty = computed(() => {
    return !fullNameFilter.value && !emailFilter.value;
});

const someFiltersNotEmpty = computed(() => {
    return !!fullNameFilter.value || !!emailFilter.value;
});

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'New user' : 'Edit user';
});


/*
 * Watch
 */
watch(dialog, (val) => {
    if (!val) {
        close();
    }
});

/*
 * Methods
 */
// Common methods
const close = () => {
    dialog.value = false;
    dialogDeleteItem.value = false;
    manageForm.reset();
    manageForm.clearErrors();
    editedIndex.value = -1;

    errors.value = {};
};

const goToPage = async ({ page, itemsPerPage, sortBy, clearFilters }) => {
    // TODO: Rewrite!!!
    if (clearFilters) {
        fullNameFilter.value = null;
        emailFilter.value = null;
    }
    console.log({page, itemsPerPage})

    // TODO: Rewrite!!!
    if (
        allFiltersEmpty ||
        someFiltersNotEmpty
    ) {
        let options = { data: { page: page, per_page: itemsPerPage } };

        if (sortBy && sortBy.length > 0) {
            options.data.order_by = sortBy[0].key;
            options.data.sort = sortBy[0].order;
        }

        // Search filters
        options.data.full_name = fullNameFilter.value;
        options.data.email = emailFilter.value;

        await router.reload(Object.assign(options, {
            onCancelToken: cancelToken => {},
            onCancel: () => {},
            onBefore: visit => {
                loading.value = true;
            },
            onStart: visit => {},
            onProgress: progress => {},
            onSuccess: page => {
                currentPage.value = options.data.page;
                perPage.value = options.data.per_page;
            },
            onError: errors => {
                console.log(errors);
            },
            onFinish: visit => {
                loading.value = false;
            },
        }));
    }
};


// Create/edit item methods
const editItem = (item) => {
    editedIndex.value = modifiedItems.value.indexOf(item);

    Object.assign(manageForm, {
        id: item.id,
        first_name: item.first_name,
        last_name: item.last_name,
        email: item.email,
        password: item.password,
        role: item.roles.length > 0 ? item.roles[0].id : null,
    });

    dialog.value = true;
};

const manageForm = useForm({
    id: null,
    first_name: null,
    last_name: null,
    email: null,
    password: null,
    password_confirmation: null,
    role: null,
});

const manageItem = async () => {
    manageForm.processing = true;

    if (!manageForm.password) {
        delete manageForm.password;
    }

    if (!manageForm.password_confirmation) {
        delete manageForm.password_confirmation;
    }

    let formOptions = {
        // preserveScroll: true,
        // preserveState: true,
        // resetOnSuccess: false,
        onSuccess: (page) => {
            close();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            manageForm.processing = false;

            goToPage({ page: currentPage.value, itemsPerPage: perPage.value, sortBy: orderBy.value });
        },
    };

    if (editedIndex.value === -1) {
        manageForm.post(route('users.store'), formOptions);
    } else {
        manageForm.put(route('users.update', {user: manageForm.id}), formOptions);
    }
};


// Delete item methods
const openDeleteItemDialog = (item) => {
    deleteForm.id = item.id;
    dialogDeleteItem.value = true
};

const deleteForm = useForm({
    id: null,
});

const deleteItem = async () => {
    deleteForm.processing = true;

    let formOptions = {
        onSuccess: (page) => {
            close();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            deleteForm.processing = false;

            goToPage({ page: currentPage.value, itemsPerPage: perPage.value, sortBy: orderBy.value });
        },
    };

    deleteForm.delete(route('users.destroy', { id: deleteForm.id }), formOptions);
};


// Other methods
const downloadFile = async (filename = '') => {
    NProgress.start();
    fileInProgress.value = true;

    try {
        const response = await axios.post(route('users.download'), {}, { responseType: 'blob' });

        const contentType = response.headers['content-type'];
        const fileExtension = mimeDb[contentType.toLowerCase()] && mimeDb[contentType.toLowerCase()].extensions
            ? '.' + mimeDb[contentType.toLowerCase()].extensions
            : '';

        let sanitizedFilename;

        if (filename) {
            sanitizedFilename = filename + fileExtension;
        } else {
            const contentDisposition = response.headers['content-disposition'];
            const filenameIndex = contentDisposition.indexOf('filename=');

            if (filenameIndex !== -1) {
                const filename = contentDisposition.slice(filenameIndex + 9);
                sanitizedFilename = filename.replace(/['"]/g, '');
            } else {
                sanitizedFilename = uuidv4() + fileExtension;
            }
        }

        const blob = new Blob([response.data], { type: response.headers['content-type'] });
        const link = document.createElement('a');

        link.href = window.URL.createObjectURL(blob);
        link.download = sanitizedFilename;

        link.click();
    } catch (error) {
        console.error(error);
    }

    fileInProgress.value = false;
    NProgress.done();
};
</script>

<template>
    <Head title="Users"/>

    <AuthenticatedLayout :errors="errors" :showLoader="loading">
        <template #header>
            <h2 class="tw-font-semibold tw-text-xl tw-text-gray-800 tw-leading-tight">Users</h2>

            <div class="tw-flex tw-items-center tw-justify-end">
                <v-hover v-slot:default="{ isHovering, props }">
                    <v-btn v-bind="props" :color="isHovering ? 'accent' : 'primary'" dark>
                        Add new user

                        <v-dialog v-model="dialog" activator="parent" width="80vw">
                            <v-card height="80vh">
                                <v-card-title>
                                    <span class="tw-text-h5">{{ formTitle }}</span>
                                </v-card-title>

                                <v-card-text>
                                    <v-container>
                                        <v-row>
                                            <v-col cols="12" sm="6">
                                                <v-text-field
                                                    type="text"
                                                    autocomplete="given-name"
                                                    v-model="manageForm.first_name"
                                                    :error-messages="errors.first_name"
                                                    label="First Name"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" sm="6">
                                                <v-text-field
                                                    type="text"
                                                    autocomplete="family-name"
                                                    v-model="manageForm.last_name"
                                                    :error-messages="errors.last_name"
                                                    label="Last Name"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>

                                        <v-row>
                                            <v-col cols="12" md="4" sm="6">
                                                <v-text-field
                                                    type="email"
                                                    autocomplete="email"
                                                    v-model="manageForm.email"
                                                    :error-messages="errors.email"
                                                    label="Email"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" sm="4">
                                                <v-text-field
                                                    type="password"
                                                    v-model="manageForm.password"
                                                    :error-messages="errors.password"
                                                    label="Password"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" sm="4">
                                                <v-text-field
                                                    type="password"
                                                    v-model="manageForm.password_confirmation"
                                                    :error-messages="errors.password_confirmation"
                                                    label="Password Confirmation"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>

                                        <v-row>
                                            <v-col cols="12" sm="4">
                                                <v-select
                                                    v-model="manageForm.role"
                                                    :items="roles"
                                                    :error-messages="errors.role"
                                                    item-title="human_name"
                                                    item-value="id"
                                                    label="Role"
                                                    required
                                                    :disabled="manageForm.processing"
                                                ></v-select>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-hover v-slot:default="{ isHovering, props }">
                                        <v-btn @click="close" v-bind="props" :color="isHovering ? 'accent' : 'primary'" variant="elevated" :disabled="manageForm.processing">Cancel</v-btn>
                                    </v-hover>
                                    <v-hover v-slot:default="{ isHovering, props }">
                                        <v-btn @click="manageItem" v-bind="props" :color="isHovering ? 'accent' : 'primary'" :disabled="manageForm.processing">Save</v-btn>
                                    </v-hover>

                                    <v-btn-primary @click="close" v-bind="props" :disabled="manageForm.processing">Cancel</v-btn-primary>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </v-btn>
                </v-hover>
            </div>

            <v-dialog v-model="dialogDeleteItem" activator="parent" width="20vw">
                <v-card height="30vh">
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <p>Are you sure that you want to delete current user?</p>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-hover v-slot:default="{ isHovering, props }">
                            <v-btn @click="close" v-bind="props" :color="isHovering ? 'accent' : 'primary'" :disabled="deleteForm.processing">Cancel</v-btn>
                        </v-hover>
                        <v-hover v-slot:default="{ isHovering, props }">
                            <v-btn-primary @click="deleteItem" v-bind="props" :color="isHovering ? 'accent' : 'primary'" :disabled="deleteForm.processing">Delete</v-btn-primary>
                        </v-hover>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </template>

        <div class="tw-table-block tw-max-w-full tw-mx-auto tw-py-6 tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-bg-white tw-flex tw-justify-between tw-px-6 tw-py-6">
                <div class="tw-w-full">
                    <v-row>
                        <v-col cols="12" sm="3">
                            <v-text-field
                                type="text"
                                autocomplete="off"
                                v-model="fullNameFilter"
                                label="Name"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="12" sm="3">
                            <v-text-field
                                type="text"
                                autocomplete="off"
                                v-model="emailFilter"
                                label="Email"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </div>

                <div class="tw-ml-6">
                    <v-hover v-slot:default="{ isHovering, props }">
                        <v-btn
                            class="tw-mt-2"
                            v-bind="props"
                            :color="isHovering ? 'accent' : 'primary'"
                            @click="search = String(Date.now())"
                            dark
                        >Search</v-btn>
                    </v-hover>
                </div>
            </div>

            <v-data-table-server
                v-model:items-per-page="perPage"
                :items-per-page-options="[
                    {value: 10, title: '10'},
                    {value: 25, title: '25'},
                    {value: 50, title: '50'},
                    {value: 100, title: '100'},
                    {value: -1, title: '$vuetify.dataFooter.itemsPerPageAll'}
                ]"
                :headers="tableHeaders"
                :page="currentPage"
                :items-length="totalItems"
                :items="modifiedItems"
                :search="search"
                class="data-table-container elevation-1"
                item-value="name"
                v-sortable-data-table
                @update:options.sync="goToPage"
            >
                <template v-slot:item="{ item }">
                    <tr>
                        <td>{{item.selectable.first_name}}</td>

<!--                        <td>{{formatDate(item.selectable.creationDate)}}</td>-->

                        <td>{{item.selectable.last_name}}</td>

                        <td>{{item.selectable.email}}</td>

                        <td>{{item.selectable.primary_role_name}}</td>

                        <td>
                            <v-tooltip location="top">
                                <template v-slot:activator="{ props }">
                                    <v-icon v-bind="props" size="small" class="tw-me-2" @click="editItem(item.raw)">mdi-pencil</v-icon>
                                </template>
                                <span>Edit user</span>
                            </v-tooltip>

                            <v-tooltip location="top">
                                <template v-slot:activator="{ props }">
                                    <v-icon v-bind="props" size="small" class="tw-me-2" @click="downloadFile()">mdi-download</v-icon>
                                </template>
                                <span>Download user info</span>
                            </v-tooltip>

                            <v-tooltip location="top">
                                <template v-slot:activator="{ props }">
                                    <v-icon v-bind="props" size="small" class="tw-me-2" @click="openDeleteItemDialog(item.raw)">mdi-delete</v-icon>
                                </template>
                                <span>Delete User</span>
                            </v-tooltip>
                        </td>
                    </tr>
                </template>

                <template v-slot:no-data>
                    <div class="tw-py-6">
                        <template v-if="loading">
                            <v-progress-circular indeterminate :size="40"></v-progress-circular>
                        </template>
                        <template v-else>
                            <template v-if="allFiltersEmpty">
                                <h3 class="tw-mb-4">Table is empty</h3>
                            </template>
                            <template v-else>
                                <h3 class="tw-mb-4">Table is empty. Please, reset search filters</h3>

                                <v-btn color="primary" @click="goToPage({ page: 1, itemsPerPage: perPage, clearFilters: true })">Reset</v-btn>
                            </template>
                        </template>
                    </div>
                </template>
            </v-data-table-server>
        </div>
    </AuthenticatedLayout>
</template>
