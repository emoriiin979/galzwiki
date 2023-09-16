<script setup>
/**
 * アプリケーション名（configから取得）
 * @type {string}
 */
const appName = import.meta.env.VITE_APP_NAME;

/**
 * 強調色（configから取得）
 * @type {string}
 */
const accentColor = import.meta.env.VITE_SITE_ACCENT_COLOR;

/**
 * メニューに表示する内容
 * @type {{label: string, href: string, showFlag: string|null}[]}
 */
const links = [
    { label: 'Login', href: '/login', showFlag: "showLoginButton" },
];

/**
 * メニュー内容の表示チェック
 * @param {label: string, href: string, showFlag: string|null} link
 */
const isShowLinkButton = (link) => {
    const url = new URL(window.location.href);
    return !link.showFlag || url.searchParams.has(link.showFlag);
}
</script>

<template>
    <v-app-bar app :color="accentColor" flat>
        <v-container>
            <v-row>
                <v-col cols="auto">
                    <v-btn href="/wiki/search" style="text-transform: none">
                        {{ appName }}
                    </v-btn>
                </v-col>
                <v-spacer />
                <v-col cols="auto">
                    <v-btn v-for="link in links" :key="link" :href="link.href">
                        <div v-if="isShowLinkButton(link)">
                            {{ link.label }}
                        </div>
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
    </v-app-bar>
</template>
