<template>
    <div>
        <v-data-table
            :items="postings"
            :headers="headers"
        >
            <template v-slot:item.reason="{item}">
                <span v-if="!item.revision">
                    Создано вручную
                </span>
                <span v-else>
                    На основании ревизии №{{ item.revision.id }} от {{ item.revision.date }}
                    <v-btn icon @click="$router.push(`/revision/${item.revision.id}`)">
                        <v-icon>mdi-eye</v-icon>
                    </v-btn>
                </span>
            </template>
            <template v-slot:item.actions="{item}">
                <v-btn icon @click="onShow(item.id)">
                    <v-icon>mdi-eye</v-icon>
                </v-btn>
                <v-btn
                    @click="onConfirm(item)"
                    icon
                    color="success"
                    v-if="item.status === 1"
                    title="Подтвердить">
                    <v-icon>mdi-check</v-icon>
                </v-btn>
                <v-btn
                    @click="onDecline(item)"
                    icon
                    color="error"
                    v-if="[1].includes(item.status)"
                    title="Отменить">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
        </v-data-table>
        <PostingModal
            :state="showPostingModal"
            @cancel="showPostingModal = false"
        />
    </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';
import PostingModal from '@/components/Modal/Posting/PostingModal';

export default {
    components: {PostingModal},
    data: () => ({
        headers: [
            {
                value: 'created_at',
                text: 'Дата создания'
            },
            {
                value: 'document_no',
                text: 'Номер'
            },
            {
                value: 'store.name',
                text: 'Склад'
            },
            {
                value: 'user.name',
                text: 'Инициатор'
            },
            {
                value: 'reason',
                text: 'Основание'
            },
            {
                value: 'description',
                text: 'Описание'
            },
            {
                value: 'status_text',
                text: 'Текущий статус'
            },
            {
                value: 'actions',
                text: 'Действия'
            },
        ],
        showPostingModal: false,
    }),
    computed: {
        ...mapGetters({
            $postings: 'POSTINGS'
        }),
        postings () {
            return this.$postings;
        }
    },
    async mounted () {
        this.$loading.enable();
        await this.$getPostings();
        this.$loading.disable();
    },
    methods: {
        ...mapActions({
            $getPostings: 'getPostings',
            $getPosting: 'getPosting',
            $confirmPosting: 'postingConfirm',
            $declinePosting: 'postingDecline',
        }),
        async onShow (id) {
            this.showPostingModal = true;
            this.$loading.enable();
            await this.$getPosting(id);
            this.$loading.disable();
        },
        async onConfirm (item) {
            await this.$confirm('Вы действительно хотите подтвердить выбранное оприходование?')
                .then(async () => {
                    this.$loading.enable();
                    await this.$confirmPosting(item.id);
                    this.$toast.success('Оприходование успешно одобрено!')
                    this.$loading.disable();
                });
        },
        async onDecline (item) {
            await this.$confirm('Вы действительно хотите отклонить выбранное оприходование?')
                .then(async () => {
                    this.$loading.enable();
                    await this.$declinePosting(item.id);
                    this.$toast.success('Оприходование успешно отклонено!')
                    this.$loading.disable();
                });
        }
    }
}
</script>

<style scoped lang="scss">

</style>
