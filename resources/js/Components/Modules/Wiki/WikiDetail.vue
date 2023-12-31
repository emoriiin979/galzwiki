<script setup>
import axios from "axios";
import { ref, onMounted, computed } from "vue";

/**
 * ページID
 * @type {number}
 */
const pageId = computed(() => {
    const url = new URL(location.href);
    return url.searchParams.get('page_id');
});

/**
 * 事項の詳細データ
 * @type {object}
 */
const entry = ref({});

/**
 * 削除確認ダイアログの表示フラグ
 * @type {boolean}
 */
const deleteDialog = ref(false);

/**
 * ページ遷移
 * @param {string}
 */
const jump = (mode) => {
    const url = location.origin + '/wiki/edit?page_id=' + pageId.value;
    location.href = url + '&mode=' + mode;
}

/**
 * 削除実行
 * @param {object}
 */
const commitDelete = (entry) => {
    axios.delete('/api/entries/' + pageId.value);
    location.href = location.origin + '/wiki/detail?page_id=' + entry.parent_entry_id;
}

/**
 * 初回実行
 */
axios
    .get('/api/entries/' + pageId.value)
    .then((result) => {
        entry.value = result.data.data;
    })
    .catch((error) => {
        // nop.
    });
</script>

<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12">
                <v-dialog
                    v-model="deleteDialog"
                    persistent
                    width="512"
                >
                    <v-card
                        class="px-8 py-4"
                    >
                        <v-card-text>
                            削除しますか？
                        </v-card-text>
                        <v-card-actions>
                            <v-btn
                                color="orange-lighten-3"
                                @click="commitDelete(entry)"
                            >
                                Yes
                            </v-btn>
                            <v-spacer />
                            <v-btn
                                color="grey"
                                @click="deleteDialog = false"
                            >
                                No
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-sheet
                  class="d-flex mx-auto px-8 py-8"
                  rounded
                >
                    <div v-if="entry.id">
                        <h2 class="text-h4 text-orange">
                            {{ entry.title }}
                        </h2>
                        <div class="text-h5 text-grey">
                            {{ entry.subtitle }}
                        </div>
                        <p class="text-body-2">
                            {{ entry.body }}
                        </p>
                        <v-divider
                            class="my-4"
                        />
                        <v-btn
                            class="me-4"
                            type="button"
                            color="orange-lighten-3"
                            variant="flat"
                            @click="jump('add')"
                        >
                            作成
                        </v-btn>
                        <v-btn
                            class="me-4"
                            type="button"
                            color="orange-lighten-3"
                            variant="flat"
                            @click="jump('edit')"
                        >
                            編集
                        </v-btn>
                        <v-btn
                            class="me-4"
                            type="button"
                            color="orange-lighten-3"
                            variant="flat"
                            @click="deleteDialog = true"
                        >
                            削除
                        </v-btn>
                    </div>
                    <div v-else>
                        <v-alert
                            type="info"
                            text="データが存在しません。"
                            variant="tonal"
                        />
                    </div>
                </v-sheet>
            </v-col>
        </v-row>
    </v-container>
</template>
