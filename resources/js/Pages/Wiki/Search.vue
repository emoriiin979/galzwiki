<script setup>
import { Head } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import WikiList from '@/Components/Modules/Wiki/WikiList.vue';
</script>

<template>
    <Head title="検索" />

    <base-layout>
        <v-row>
            <v-col cols="9">
                <wiki-list />
            </v-col>
            <v-col cols="3">
                <!-- -->
            </v-col>
        </v-row>
    </base-layout>
</template>
