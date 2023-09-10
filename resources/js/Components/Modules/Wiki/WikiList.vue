<script setup>
import { onMounted } from '@vue/runtime-core';
import { ref } from 'vue';
import axios from 'axios';

const response = ref({
    meta: {
        last_page: 10,
    },
});
const params = ref({
    keywords: '',
    page: 1,
});
const find = (page) => {
    params.value.page = page;
    axios
        .get('/api/entries', {
            params: {
                page: params.value.page,
                keywords: params.value.keywords.split(/ |　/),
            },
        })
        .then((result) => {
            response.value = result.data;
        });
};
const showTopicList = (entry) => {
    let buf = [];
    entry.parents.sort((a, b) => {
        return (a.depth < b.depth) ? -1 : 1;
    }).map((parent) => {
        buf.push(parent.title);
    });
    buf.push(entry.title);
    return buf.join(' > ');
};
onMounted(() => {
    find(1);
});
</script>

<template>
    <v-container fluid>
        <v-row align="center">
            <v-col cols="12">
                <v-form @submit.prevent>
                    <v-text-field
                        v-model="params.keywords"
                        bg-color="white"
                        variant="outlined"
                        label="Free Keywords"
                        append-inner-icon="mdi-magnify"
                        @click:append-inner="find(1)"
                        @keyup.enter="find(1)"
                    />
                </v-form>
            </v-col>
        </v-row>
        <v-row align="center">
            <v-col v-if="response.data" cols="12">
                <v-pagination
                    v-model="params.page"
                    :length="response.meta.last_page"
                    :total-visible="6"
                    @update:modelValue="find(params.page)"
                />
                <v-card
                    class="my-8"
                    flat
                    v-for="entry in response.data"
                    :key="entry"
                    :href="'/wiki/detail/' + entry.id"
                >
                    <v-card-title>
                        {{ entry.title }}
                    </v-card-title>
                    <v-card-subtitle>
                        {{ entry.subtitle }}
                    </v-card-subtitle>
                    <v-card-text>
                        {{ showTopicList(entry) }}
                    </v-card-text>
                </v-card>
                <v-pagination
                    v-model="params.page"
                    :length="response.meta.last_page"
                    :total-visible="6"
                    @update:modelValue="find(params.page)"
                />
            </v-col>
        </v-row>
    </v-container>
</template>