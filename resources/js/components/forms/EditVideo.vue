<script setup>
import { toCalendarFormattedDate } from '@/service/util';
import { reactive, ref, watch } from 'vue';
import { useGetVideoTags } from '@/service/queries';
import { UseCreateTag } from '@/service/mutations';
import { toast } from '@/service/toaster/toastService';

import FormInputNumber from '@/components/inputs/FormInputNumber.vue';
import InputMultiChip from '@/components/pinesUI/InputMultiChip.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormTextArea from '@/components/inputs/FormTextArea.vue';
import DatePicker from '@/components/pinesUI/DatePicker.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import mediaAPI from '@/service/mediaAPI.ts';
import useForm from '@/composables/useForm';

const emit = defineEmits(['handleFinish']);
const props = defineProps(['video']);

const { data: tagsQuery } = useGetVideoTags();
const createTag = UseCreateTag();

const allTags = ref([]);
const fields = reactive([
    {
        name: 'title',
        text: 'Title',
        type: 'text',
        required: true,
        value: props.video?.title,
        default: props.video?.name,
        subtext: 'The intended title of the video',
        max: 255,
    },
    {
        name: 'description',
        text: 'Description',
        type: 'textArea',
        value: props.video?.description,
        default: '',
    },
    {
        name: 'episode',
        text: 'Episode',
        type: 'number',
        value: props.video?.episode ?? 1,
        default: 0,
        min: 0,
    },
    {
        name: 'season',
        text: 'Season',
        type: 'number',
        value: props.video?.season ?? 1,
        default: 0,
        min: 0,
    },
    {
        name: 'poster_url',
        text: 'Video Thumbnail URL',
        type: 'url',
        value: props.video?.metadata?.poster_url,
        subtext: 'A thumbnail associated with the video',
        default: null,
    },
    {
        name: 'date_released',
        text: 'Date Release',
        type: 'date',
        value: props.video?.date_released ? toCalendarFormattedDate(props.video?.date_released) : null,
        default: null,
    },
    {
        name: 'video_tags',
        text: 'Tags',
        type: 'multi',
        value: props.video?.video_tags ?? [],
        default: props.video?.video_tags ?? [],
        subtext: 'Tags that describe the video',
        max: 24,
    },
]);

const form = useForm({
    title: props.video?.title ?? props.video?.name,
    description: props.video?.description ?? '',
    episode: props.video?.episode?.toString() ?? '',
    season: props.video?.season?.toString() ?? '',
    poster_url: props.video?.metadata.poster_url ?? '',
    date_released: props.video?.date_released ? toCalendarFormattedDate(props.video?.date_released) : '',
    video_tags: props.video?.video_tags ?? [],
    deleted_tags: [],
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (props.video?.metadata?.id) {
                return mediaAPI.updateMetadata(props.video.metadata.id, fields);
            } else return mediaAPI.createMetadata({ ...fields, video_id: props.video.id });
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

const handleCreateTag = async (name) => {
    try {
        const { data: response } = await createTag.mutateAsync({ name });

        toast.add('Success', { type: 'success', description: 'Tag created!', life: 3000 });
        form.fields['video_tags'] = [...form.fields['video_tags'], { id: response.id, name: response.name }];
    } catch (error) {
        console.log(error);

        toast.add('Error', { type: 'error', description: 'Unable to create tag. It may already exist.', life: 3000 });
    }
};

const handleSetTags = (tags) => {
    form.fields['video_tags'] = [...tags];
    form.fields['deleted_tags'] = form.fields['deleted_tags'].filter((itm) => !tags.find((newTag) => newTag.name === itm.name));
};

const handleRemoveTag = (tag) => {
    form.fields['video_tags'] = form.fields['video_tags'].filter((itm) => itm.name !== tag.name);

    if (tag.video_tag_id) form.fields['deleted_tags'] = [...form.fields['deleted_tags'], tag.video_tag_id];
};

watch(tagsQuery, () => {
    if (tagsQuery.value?.data?.data) {
        allTags.value = tagsQuery.value.data.data; // Array of tag resources
    }
});
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class="w-full" :class="field.class">
            <FormInputLabel :field="field" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" :field="field" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <InputMultiChip
                v-else-if="field.name === 'video_tags'"
                :placeholder="'Add tags'"
                :defaultItems="form.fields[field.name] ? form.fields[field.name] : []"
                :options="allTags"
                :max="field.max"
                @createAction="handleCreateTag"
                @selectItems="handleSetTags"
                @removeAction="handleRemoveTag"
            />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />
            <ul class="text-sm text-rose-600 dark:text-rose-400">
                <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
            </ul>
        </div>
        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button
                @click="$emit('handleFinish')"
                type="button"
                tabindex="97"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                tabindex="98"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                :disabled="form.processing"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
