<script setup>
import CommonHeader from '@/Components/Parts/Common/CommonHeader.vue';
</script>

<template>
    <v-app>
        <common-header />
        <v-main class="bg-grey-lighten-5">
            <v-container>
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>
