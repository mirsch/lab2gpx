<script setup lang="ts">
import type { Settings } from '@/interfaces/Settings.ts';
import { useI18n } from 'vue-i18n';
import { reactive, watch } from 'vue';
import { useDownload } from '@/composables/useDownload.ts';
import ModalWrap from '@/components/ModalWrap.vue';
import AwesomeIcon from '@/components/AwesomeIcon.vue';

const { t, locale } = useI18n();

const open = defineModel<boolean>('open', {
    required: true,
});
const settings = defineModel<Settings>('settings', {
    required: true,
});

function switchLocale(l: string) {
    locale.value = l;
    form.locale = l;
    settings.value.locale = l;
}

const form = reactive(Object.assign({}, settings.value));

type FormErrors = Record<keyof Settings, string>;
const errors = reactive<FormErrors>({} as FormErrors);

const cls = (field: keyof Settings) => ({
    error: errors[field],
});

function validate() {
    // @TODO
    errors.limit =
        !isNaN(form.limit) &&
        isFinite(form.limit) &&
        Number.isInteger(form.limit) &&
        form.limit >= 0
            ? ''
            : t('settings.validation.limit');
    errors.prefix = '';
    if (!form.customCodeTemplate) {
        if (form.prefix.length < 1 || form.prefix.length > 3) {
            errors.prefix = t('settings.validation.prefix')
        }
    }
    return !errors.limit && !errors.prefix;
}

function onSave() {
    if (!validate()) {
        alert(t('settings.validation.check'))
        return;
    }
    Object.assign(settings.value, form);
    open.value = false
}

const { triggerDownload } = useDownload();

function onSaveAndDownload() {
    onSave();
    triggerDownload(settings.value)
}

watch(
    () => open.value,
    () => {
        // reset
        if (open.value) {
            Object.assign(form, settings.value);
            Object.keys(errors).forEach((k) => (errors[k as keyof Settings] = ''));
        }
    },
);
</script>

<template>
    <ModalWrap v-model:open="open">
        <template #header>{{ t('settings.header') }}</template>


            <fieldset>
                <legend>{{ t('language') }}</legend>
                <div class="form-row">
                    <button
                        class="button outline"
                        :class="{ primary: settings.locale == 'de' }"
                        @click="switchLocale('de')"
                    >
                        DE
                    </button>
                    <button
                        class="button outline"
                        :class="{ primary: settings.locale == 'en' }"
                        @click="switchLocale('en')"
                    >
                        EN
                    </button>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.general.legend') }}</legend>
                <div class="form-row">
                    <label for="settings-limit">{{ t('settings.general.limit') }}</label>
                    <input
                        id="settings-limit"
                        v-model.trim="form.limit"
                        @blur="validate()"
                        :class="cls('limit')"
                        type="number"
                        min="0"
                        step="1"
                    />
                    <p v-if="errors.limit" class="text-error nm">{{ errors.limit }}</p>
                    <p class="small">{{ t('settings.general.limit_hint') }}</p>
                </div>

                <div class="form-row">
                    <label for="settings-cache-type">{{
                            t('settings.general.cache_type')
                        }}</label>
                    <select id="settings-cache-type" v-model="form.cacheType">
                        <option value="Lab Cache">Lab Cache</option>
                        <option value="Virtual Cache">Virtual Cache</option>
                        <option value="Virtual Cache">Mega-Event Cache</option>
                    </select>
                    <p class="small">{{ t('settings.general.cache_type_hint') }}</p>
                </div>
                <div class="form-row">
                    <label for="settings-linear">{{ t('settings.general.linear_label') }}</label>
                    <select id="settings-linear" v-model="form.linear">
                        <option value="default">{{ t('settings.general.linear.default') }}</option>
                        <option value="first">{{ t('settings.general.linear.first') }}</option>
                        <option value="mark">{{ t('settings.general.linear.mark') }}</option>
                        <option value="corrected">{{ t('settings.general.linear.corrected') }}</option>
                        <option value="ignore">{{ t('settings.general.linear.ignore') }}</option>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.code.legend') }}</legend>
                <div class="form-row">
                    <label for="settings-prefix">{{ t('settings.code.prefix') }}</label>
                    <input
                        id="settings-prefix"
                        v-model.trim="form.prefix"
                        @blur="validate()"
                        :class="cls('prefix')"
                        type="text"
                        :disabled="
                                form.customCodeTemplate !== null &&
                                form.customCodeTemplate.trim() !== ''
                            "
                    />
                    <p v-if="errors.prefix" class="text-error nm">{{ errors.prefix }}</p>
                    <p class="small">{{ t('settings.code.prefix_hint')}}</p>
                </div>
                <div class="form-row">
                    <label>
                        <input
                            id="settings-stage-separator"
                            v-model="form.stageSeparator"
                            type="checkbox"
                            :disabled="
                                    form.customCodeTemplate !== null &&
                                    form.customCodeTemplate.trim() !== ''
                                "
                        />
                        {{ t('settings.code.stage_separator') }}
                    </label>
                </div>
                <div class="form-row">
                    <label for="settings-custom-code-template">{{
                            t('settings.code.custom_code_template')
                        }}</label>
                    <input
                        id="settings-custom-code-template"
                        v-model.trim="form.customCodeTemplate"
                        @blur="validate()"
                        :class="cls('customCodeTemplate')"
                        type="text"
                    />
                    <p v-if="errors.customCodeTemplate" class="text-error nm">
                        {{ errors.customCodeTemplate }}
                    </p>
                    <p
                        class="small"
                        v-html="t('settings.code.custom_code_template_hint')"
                    ></p>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.personalization.legend') }}</legend>
                <div class="form-row">
                    <label for="settings-user-guid">{{
                            t('settings.personalization.user_guid')
                        }}</label>
                    <input
                        id="settings-user-guid"
                        v-model.trim="form.userGuid"
                        @blur="validate()"
                        :class="cls('userGuid')"
                        type="text"
                    />
                    <p v-if="errors.userGuid" class="text-error nm">{{ errors.userGuid }}</p>
                    <p class="small" v-html="t('settings.personalization.user_guid_hint')"></p>
                </div>
                <div class="form-row">
                    <label>
                        <input
                            v-model="form.completionStatuses"
                            value="0"
                            type="checkbox"
                            :disabled="!form.userGuid"
                        />
                        {{t('settings.personalization.completed')}}
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <input
                            v-model="form.completionStatuses"
                            value="1"
                            type="checkbox"
                            :disabled="!form.userGuid"
                        />
                        {{t('settings.personalization.partial')}}
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <input
                            v-model="form.completionStatuses"
                            value="2"
                            type="checkbox"
                            :disabled="!form.userGuid"
                        />
                        {{t('settings.personalization.not_started')}}
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.description.legend') }}</legend>
                <div class="form-row">
                    <label> <input v-model="form.includeQuestion" type="checkbox" /> {{t('settings.description.include_question')}}</label>
                </div>
                <div class="form-row">
                    <label>
                        <input v-model="form.includeWaypointDescription" type="checkbox" /> {{t('settings.description.include_waypoint_description')}}
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <input
                            v-model="form.includeCacheDescription"
                            value="2"
                            type="checkbox"
                        />
                        {{t('settings.description.include_adventure_lab_description')}}
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.exclude.legend') }}</legend>
                <div class="form-row">
                    <label for="settings-exclude-owner">{{t('settings.exclude.owner')}}</label>
                    <textarea v-model="form.excludeOwner" rows="3"></textarea>
                    <p class="small" v-html="t('settings.exclude.owner_hint')"></p>
                </div>
                <div class="form-row">
                    <label for="settings-exclude-owner">{{t('settings.exclude.name')}}</label>
                    <textarea v-model="form.excludeNames" rows="3"></textarea>
                    <p class="small" v-html="t('settings.exclude.name_hint')"></p>
                </div>
                <div class="form-row">
                    <label for="settings-exclude-uuids">{{t('settings.exclude.uuids')}}</label>
                    <textarea v-model="form.excludeUuids" rows="3"></textarea>
                    <p class="small" v-html="t('settings.exclude.uuids_hint')"></p>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{ t('settings.quirks.legend') }}</legend>
                <div class="form-row">
                    <label> <input v-model="form.quirksL4Ctype" type="checkbox" /> {{t('settings.quirks.l4ctype')}}</label>
                </div>
                <div class="form-row">
                    <label> <input v-model="form.quirksBomForCsv" type="checkbox" /> {{t('settings.quirks.bom2csv')}}</label>
                </div>
            </fieldset>

            <fieldset>
                <legend>{{t('settings.download.legend')}}</legend>
                <div class="form-row">
                    <label for="settings-download">{{ t('settings.download.format_label') }}</label>
                    <select id="settings-linear" v-model="form.outputFormat">
                        <option value="zippedgpx">{{ t('settings.download.format.zippedgpx') }}</option>
                        <option value="gpx">{{ t('settings.download.format.gpx') }}</option>
                        <option value="zippedgpxwpt">{{ t('settings.download.format.zippedgpxwpt') }}</option>
                        <option value="gpxwpt">{{ t('settings.download.format.gpxwpt') }}</option>
                        <option value="cacheturdotno">{{ t('settings.download.format.cacheturdotno') }}</option>
                    </select>
                </div>
            </fieldset>


        <template #footer>
            <a class="button" @click="open = false" :title="t('cancel')"><AwesomeIcon icon="xmark"/></a>
            <a class="button primary" @click="onSave" :title="t('save')"><AwesomeIcon icon="save"/></a>
            <a class="button primary" @click="onSaveAndDownload" :title="t('save_and_download')"><AwesomeIcon icon="save" /><AwesomeIcon icon="download"/></a>
        </template>
    </ModalWrap>
</template>

<style scoped>
fieldset {
    margin-bottom: 1rem;
}

fieldset div {
    width: 100%;
}

fieldset .form-row {
    margin-bottom: 1rem;
}

.small {
    font-size: 80%;
}

.nm {
    margin-bottom: 0;
}
</style>
