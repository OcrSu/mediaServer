<script setup>
import { handleStorageURL, toCalendarFormattedDate } from '@/service/util';
import { reactive } from 'vue';
import { toast } from '@/service/toaster/toastService';

import FormInputNumber from '@/components/inputs/FormInputNumber.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormTextArea from '@/components/inputs/FormTextArea.vue';
import DatePicker from '@/components/pinesUI/DatePicker.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import mediaAPI from '@/service/mediaAPI.ts';
import useForm from '@/composables/useForm';

const emit = defineEmits(['handleFinish']);
const props = defineProps(['folder']);

// 'title', 'description', 'studio', 'seasons', 'episodes', 'films', 'date_start', 'date_end', 'thumbnail_url', 'editor_id';
const fields = reactive([
    {
        name: 'title',
        text: 'Title',
        type: 'text',
        required: true,
        value: props.folder?.series?.title,
        default: props.folder?.series?.title,
        max: 255,
    },
    {
        name: 'description',
        text: 'Description',
        type: 'textArea',
        value: props.folder?.series?.description,
        default: '',
    },
    {
        name: 'studio',
        text: 'Studio',
        type: 'text',
        value: props.folder?.series?.studio,
        subtext: 'The producer studio',
        default: '',
    },
    {
        name: 'episodes',
        text: 'Episodes',
        type: 'number',
        value: props.folder?.series?.episodes ?? 1,
        subtext: 'The number of episodes in the series',
        default: 0,
        min: 0,
    },
    {
        name: 'seasons',
        text: 'Seasons',
        type: 'number',
        value: props.folder?.series?.seasons ?? 1,
        subtext: 'The number of seasons in the series',
        default: 0,
        min: 0,
    },
    {
        name: 'films',
        text: 'Films',
        type: 'number',
        value: props.folder?.series?.films ?? 1,
        subtext: 'The number of films in the series',
        default: 0,
        min: 0,
    },
    {
        name: 'date_start',
        text: 'Start Date',
        type: 'date',
        value: props.folder?.series?.date_start ? toCalendarFormattedDate(props.folder?.series?.date_start) : null,
        subtext: 'The release date of the first video in the series',
        default: null,
    },
    {
        name: 'date_end',
        text: 'End Date',
        type: 'date',
        value: props.folder?.series?.date_end ? toCalendarFormattedDate(props.folder?.series?.date_end) : null,
        subtext: 'The release date of the last video in the series',
        default: null,
    },
    {
        name: 'thumbnail_url',
        text: 'Folder Thumbnail URL',
        type: 'url',
        value: handleStorageURL(props.folder?.series?.thumbnail_url),
        subtext: 'A thumbnail associated with the series',
        default: null,
    },
]);

const form = useForm({
    folder_id: props.folder.id,
    title: props.folder?.series?.title ?? props.folder?.name,
    description: props.folder?.series?.description ?? '',
    studio: props.folder?.series?.studio ?? '',
    episodes: props.folder?.series?.episodes ?? null,
    seasons: props.folder?.series?.seasons ?? null,
    films: props.folder?.series?.films ?? null,
    date_start: props.folder?.series?.date_start ? toCalendarFormattedDate(props.folder?.series?.date_start) : null,
    date_end: props.folder?.series?.date_end ? toCalendarFormattedDate(props.folder?.series?.date_end) : null,
    thumbnail_url: handleStorageURL(props.folder?.series?.thumbnail_url) ?? null,
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (!props.folder?.series?.id) {
                return mediaAPI.createSeries(fields);
            }

            return mediaAPI.updateSeries(props.folder?.series?.id, fields);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update video details.', life: 3000 });
            },
        },
    );
};
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class="w-full" :class="field.class">
            <FormInputLabel :field="field" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <ul class="text-sm text-rose-600 dark:text-rose-400">
                <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
            </ul>
        </div>
        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button
                @click="$emit('handleFinish')"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                :disabled="form.processing"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
