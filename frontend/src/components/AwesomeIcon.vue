<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library as fontAwesomeIconLibrary } from '@fortawesome/fontawesome-svg-core';

import { faGear } from '@fortawesome/free-solid-svg-icons/faGear';
import { faDownload } from '@fortawesome/free-solid-svg-icons/faDownload';
import { faQuestion} from '@fortawesome/free-solid-svg-icons/faQuestion';
import { faLocationCrosshairs } from '@fortawesome/free-solid-svg-icons/faLocationCrosshairs';
import { faXmark } from '@fortawesome/free-solid-svg-icons/faXmark';
import { faSave } from '@fortawesome/free-solid-svg-icons/faSave';

// https://fontawesome.com/icons
fontAwesomeIconLibrary.add([
    faGear,
    faDownload,
    faQuestion,
    faLocationCrosshairs,
    faXmark,
    faSave
]);

defineProps<{
    icon: string
}>();
</script>

<template>
    <FontAwesomeIcon
        :icon="icon"
    />
</template>
