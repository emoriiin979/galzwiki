<script setup>
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { ref, computed, onMounted } from "vue";

/**
 * hooks.
 */
const page = usePage();

/**
 * モード（add:登録／edit:更新）
 * @type {string}
 */
const mode = computed(() => {
    const url = new URL(window.location.href);
    const mode = url.searchParams.get('mode');
    if (['add', 'edit'].indexOf(mode) !== -1) {
        return mode;
    } else {
        return 'add';
    }
});

/**
 * 確認ダイアログの表示フラグ
 * @type {boolean}
 */
const dialog = ref(false);

/**
 * 事項の詳細データ
 * @type {object}
 */
const entry = ref(mode.value === 'add' ? {
    id: null,
    title: '',
    subtitle: '',
    body: '',
    parent_entry_id: page.props.page_id,
    post_user_id: page.props.auth.user.id,
    is_publish: false,
} : {});

/**
 * バリデーションルール
 * @type {object}
 */
const rules = {
    required: [
        value => {
            if (value) return true;
            return '必須入力です。';
        },
    ],
};

/**
 * リダイレクト
 */
const backDetail = () => {
    location.href = location.origin + '/wiki/detail/' + page.props.page_id;
}

/**
 * 登録・更新実行
 * @param {object}
 */
const commit = (entry) => {
    if (mode.value === 'add') {
        axios.post('/api/entries', entry);
        location.href = location.origin + '/wiki/search';
    } else {
        axios.patch('/api/entries/' + page.props.page_id, entry);
        backDetail();
    }
};

/**
 * 初回実行
 */
axios
    .get('/api/entries/' + page.props.page_id)
    .then((result) => {
        if (mode.value === 'edit') {
            entry.value = result.data.data;
        }
        entry.value.fetched_id = result.data.data.id;
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
                    v-model="dialog"
                    persistent
                    width="512"
                >
                    <v-card
                        class="px-8 py-4"
                    >
                        <v-card-text>
                            更新しますか？
                        </v-card-text>
                        <v-card-actions>
                            <v-btn
                                color="orange"
                                @click="commit(entry)"
                            >
                                Yes
                            </v-btn>
                            <v-spacer />
                            <v-btn
                                color="grey"
                                @click="dialog = false"
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
                    <v-form
                        v-if="entry.fetched_id"
                        class="v-col-12"
                        @submit.prevent
                    >
                        <v-text-field
                            class="v-col-9"
                            v-model="entry.title"
                            :rules="rules.required"
                            label="Title"
                            variant="underlined"
                        />
                        <v-text-field
                            class="v-col-12"
                            v-model="entry.subtitle"
                            label="SubTitle"
                            variant="underlined"
                        />
                        <v-textarea
                            class="v-col-12"
                            v-model="entry.body"
                            :rules="rules.required"
                            label="Body"
                            variant="underlined"
                        />
                        <v-switch
                            color="orange"
                            v-model="entry.is_publish"
                            hide-details
                            inset
                            :label="entry.is_publish ? '公開' : '非公開'"
                        />
                        <v-btn
                            class="me-4"
                            type="commit"
                            color="orange-lighten-3"
                            variant="flat"
                            prepend-icon="mdi-check-circle"
                            @click="dialog = true"
                        >
                            <template v-slot:prepend>
                                <v-icon color="red"/>
                            </template>
                            確定
                        </v-btn>
                        <v-btn
                            type="button"
                            color="grey"
                            variant="flat"
                            @click="backDetail()"
                        >
                            戻る
                        </v-btn>
                    </v-form>
                    <div v-else>
                        <v-alert
                            type="info"
                            :text="(mode === 'add' ? '親' : '') + 'データが存在しません。'"
                            variant="tonal"
                        />
                    </div>
                </v-sheet>
            </v-col>
        </v-row>
    </v-container>
</template>
