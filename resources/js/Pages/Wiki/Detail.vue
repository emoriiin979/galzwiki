<script setup>
import { Head } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import WikiDetail from '@/Components/Modules/Wiki/WikiDetail.vue';
</script>

<template>
    <Head title="詳細" />

    <base-layout>
        <v-row>
            <v-col cols="9">
                <wiki-detail />
            </v-col>
            <v-col cols="3">
                <!-- -->
            </v-col>
        </v-row>
    </base-layout>
</template>
