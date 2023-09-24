<script setup>
import { Head } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import WikiEdit from '@/Components/Modules/Wiki/WikiEdit.vue';
</script>

<template>
    <Head title="編集" />

    <base-layout>
        <v-row>
            <v-col cols="9">
                <wiki-edit />
            </v-col>
            <v-col cols="3">
                <!-- -->
            </v-col>
        </v-row>
    </base-layout>
</template>
