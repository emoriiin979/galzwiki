<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";

/**
 * props.
 * @type {object}
 */
const props = defineProps({
    page_id: Number,
});

/**
 * 事項の詳細データ
 * @type {object}
 */
const entry = ref({});

/**
 * マウント直後に実行
 */
onMounted(() => {
    axios
        .get('/api/entries/' + props.page_id)
        .then((result) => {
            entry.value = result.data.data;
        });
});
</script>

<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12">
                <v-sheet
                  class="d-flex mx-auto px-8 py-8"
                  rounded
                >
                    <div>
                        <h2 class="text-h4 text-orange">
                            {{ entry.title }}
                        </h2>
                        <div class="text-h5 text-grey">
                            {{ entry.subtitle }}
                        </div>
                        <p class="text-body-2">
                            {{ entry.body }}
                        </p>
                    </div>
                </v-sheet>
            </v-col>
        </v-row>
    </v-container>
</template>
