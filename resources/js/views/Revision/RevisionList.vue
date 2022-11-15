<template>
    <div>
        <confirmation-dialog  ref="confirm"/>
        <v-text-field
            class="mt-2"
            v-model="search"
            solo
            clearable
            label="Поиск"
            single-line
            hide-details
        ></v-text-field>
        <v-data-table
            :items="filteredRevisions"
            :headers="headers"
            :search="search"
        >
            <template v-slot:item.dates="{ item }">
                <v-list>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ item.date }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                Дата создания
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ item.sent_to_approve_at }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                Дата отправки на проверку
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ item.edited_pivot_at }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                Дата редактирования сводной таблица
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ item.finished_at }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                Дата завершения
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </template>
            <template v-slot:item.files="{ item }">
                <v-list>
                    <v-list-item v-for="file of item.files" :key="file.name">
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ file.name }}
                                <v-btn :href="file.value" color="success" icon>
                                    <v-icon>mdi-download</v-icon>
                                </v-btn>
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </template>
            <template v-slot:item.actions="{ item }">
                <div class="d-flex flex-column">
                     <span v-if="item.has_not_actions && !IS_SUPERUSER">
                        Нет доступных действий
                        </span>
                    <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="d-none" ref="sentToApproveFileInput" @change="sentToApprove">
                    <v-btn text color="success" v-if="item.can_sent_to_approve" @click="revisionId = item.id; $refs.sentToApproveFileInput.click()">
                        Отправить на проверку <v-icon>mdi-check</v-icon>
                    </v-btn>
                    <v-btn text color="success" v-if="item.can_generate_pivot_table" @click="generatePivotTable(item.id)">
                        Сгенерировать промежуточную сводную таблицу <v-icon>mdi-check</v-icon>
                    </v-btn>
                    <v-btn text v-if="IS_SUPERUSER" @click="onRevisionDelete(item.id)" color="error">
                        Удалить ревизию<v-icon>mdi-close</v-icon>
                    </v-btn>
                    <div v-if="item.can_finish">
                        <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="d-none" ref="editRevisionFileInput" @change="editRevision">
                        <v-btn v-if="item.can_edit" text color="success" @click="revisionId = item.id; $refs.editRevisionFileInput.click()">
                            Загрузить исправления <v-icon>mdi-check</v-icon>
                        </v-btn>
                        <v-btn v-if="item.can_rollback" text color="error" @click="rollbackRevision(item.id)">
                            Откатить исправления <v-icon>mdi-backup-restore</v-icon>
                        </v-btn>
                        <v-btn text color="success" @click="finishRevision(item.id)">
                            Завершить без исправления <v-icon>mdi-check</v-icon>
                        </v-btn>
                    </div>
                    <v-btn v-if="item.is_finished" color="primary" text @click="$router.push(`/revision/${item.id}`)">
                        Подробная информация <v-icon>mdi-information-outline</v-icon>
                    </v-btn>
                </div>
            </template>
        </v-data-table>
    </div>
</template>

<script>
import axios from 'axios';
import ConfirmationDialog from '@/components/Modal/ConfirmationDialog';
export default {
    components: {ConfirmationDialog},
    async mounted () {
      await this.getRevisions();
    },
    data: () => ({
        revisionId: null,
        headers: [
            {
                value: 'store.name',
                text: 'Склад'
            },
            {
                value: 'user.name',
                text: 'Ответственный'
            },
            {
                value: 'dates',
                text: 'История'
            },
            {
                value: 'status_text',
                text: 'Статус'
            },
            {
                value: 'checking_user.name',
                text: 'Проверяющий'
            },
            {
                value: 'files',
                text: 'Файлы'
            },
            {
                value: 'actions',
                text: 'Действие'
            }
        ],
        search: '',
    }),
    computed: {
        revisions () {
            return this.$store.getters.revisions;
        },
        filteredRevisions () {
            return this.revisions;
        }
    },
    methods: {
        async getRevisions () {
            const { data: { data } } = await axios.get('/api/v2/revision');
            this.$store.commit('setRevisions', data);
        },
        async sentToApprove (e) {
            try {
                this.$loading.enable();
                const excelFile = e.target.files[0];
                if (!excelFile) {
                    return null;
                }
                this.$refs.sentToApproveFileInput.files = null;
                const formData = new FormData;
                formData.append('file', excelFile);
                await axios.post(`/api/v2/revision/to-approve/${this.revisionId}`, formData);
                this.$toast.success('Ревизия отправлена на проверку! Вернитесь на эту страницу позже');
                // this.$store.commit('updateRevision', data);
                await this.$store.dispatch('AUTH');
                this.revisionId = null;
            } catch (e) {
                console.log(e);
                this.$toast.error('Произошла ошибка!');
            } finally {
                this.$loading.disable();
            }
        },
        async generatePivotTable (id) {
            try {
                this.$loading.enable();
                const { data: { revision, path } } = await axios.get(`/api/v2/revision/generate-pivot/${id}`);
                this.$store.commit('updateRevision', revision);
                this.$file.download(path);
            } catch (e) {
                this.$toast.error('Произошла ошибка!');
            } finally {
                this.$loading.disable();
                this.$toast.success('Сводная таблица успешно сгенерирована!');
            }
        },
        async finishRevision (id) {
            try {
                this.$loading.enable();
                const { data: { revision, path } } = await axios.get(`/api/v2/revision/finish/${id}`);
                this.$file.download(path);
                this.$store.commit('updateRevision', revision);
            } catch (e) {
                this.$toast.error('Произошла ошибка!');
            } finally {
                this.$loading.disable();
                this.$toast.success('Ревизия успешно завершена!');
            }
        },
        async editRevision (e) {
            try {
                this.$loading.enable();
                const file = e.target.files[0];
                if (!file) {
                    return null;
                }
                this.$refs.editRevisionFileInput.files = null;
                const formData = new FormData;
                formData.append('file', file);
                const { data: { data } } = await axios.post(`/api/v2/revision/edit/${this.revisionId}`, formData);
                this.$store.commit('updateRevision', data);
                this.revisionId = null;
                this.$toast.success('Исправление успешно загружено!');
            } catch (e) {
                console.log(e);
            } finally {
                this.$loading.disable();
            }
        },
        async rollbackRevision (id) {
            try {
                this.$loading.enable();
                const { data: { data } } = await axios.post(`/api/v2/revision/rollback/${id}`);
                this.$store.commit('updateRevision', data);
                this.$toast.success('Исправление успешно откачено!');
            } catch (e) {
                console.log(e);
            } finally {
                this.$loading.disable();
            }
        },
        async onRevisionDelete (id) {
            this.$refs.confirm.open('Вы действительно хотите удалить выбранную ревизию?')
                .then(async () =>  {
                    try {
                        this.$loading.enable();
                        await axios.delete(`/api/v2/revision/${id}`);
                        this.$store.commit('deleteRevision', id);
                        this.$toast.success('Ревизия удалена!');
                        await this.$store.dispatch('AUTH');
                    } catch (e) {
                        console.log(e);
                    } finally {
                        this.$loading.disable();
                    }
                });
        }
    }
}
</script>

<style scoped lang="scss">

</style>
