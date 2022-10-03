<template>
    <div>
        <v-data-table
            :items="writeOffs"
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
        <WriteOffInfoModal
            :state="showWriteOffModal"
            @cancel="showWriteOffModal = false"
        />
    </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';
import WriteOffInfoModal from '@/components/Modal/WriteOff/WriteOffInfoModal';

export default {
    components: {WriteOffInfoModal},
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
        showWriteOffModal: false,
    }),
    computed: {
        ...mapGetters({
            $writeOffs: 'WRITE_OFFS'
        }),
        writeOffs () {
            return this.$writeOffs;
        }
    },
    async mounted () {
        this.$loading.enable();
        await this.$getWriteOffs();
        this.$loading.disable();
    },
    methods: {
        ...mapActions({
            $getWriteOffs: 'getWriteOffs',
            $getWriteOff: 'getWriteOff',
            $confirmWriteOff: 'writeOffConfirm',
            $declineWriteOff: 'writeOffDecline',
        }),
        async onShow (id) {
            this.showWriteOffModal = true;
            this.$loading.enable();
            await this.$getWriteOff(id);
            this.$loading.disable();
        },
        async onConfirm (item) {
            await this.$confirm('Вы действительно хотите подтвердить выбранное списание?')
                .then(async () => {
                    this.$loading.enable();
                    await this.$confirmWriteOff(item.id);
                    this.$loading.disable();
                });
        },
        async onDecline (item) {
            await this.$confirm('Вы действительно хотите отклонить выбранное списание?')
                .then(async () => {
                    this.$loading.enable();
                    await this.$declineWriteOff(item.id);
                    this.$loading.disable();
                });
        }
    }
}
</script>

<style scoped lang="scss">

</style>
