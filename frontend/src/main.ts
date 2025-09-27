import { createApp } from 'vue';
import App from './App.vue';
import 'chota/dist/chota.css';
import './assets/main.css';
import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import de from './locales/de.json';
import { hasStoredSettings, useSettings } from '@/composables/useSettings.ts';


function detectLocale() {
    const settings = useSettings();
    const langs = ['de', 'en'];
    if (hasStoredSettings() && settings && langs.includes(settings.value.locale)) {
        return settings.value.locale;
    }

    const nav = navigator.language?.split('-')[0];
    const l = langs.includes(nav) ? nav : 'en';
    settings.value.locale = l;
    return l;
}

const i18n = createI18n({
    locale: detectLocale(),
    fallbackLocale: 'en',
    globalInjection: true,
    messages: {
        de: de,
        en: en,
    },
});

const app = createApp(App);
app.use(i18n);
app.mount('#app');
