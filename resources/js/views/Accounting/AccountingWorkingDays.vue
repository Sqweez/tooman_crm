<template>
    <div>
        <t-card-page title="Отчеты кассовых смен">
            <v-row>
                <v-col cols="12" md="8" xl="4">
                    <v-select
                        label="Магазин"
                        :items="$stores"
                        item-value="id"
                        item-text="name"
                        v-model="storeId"
                    />
                    <v-select
                        label="Период"
                        :items="monthsList"
                        item-value="value"
                        item-text="name"
                        v-model="date"
                    />
                    <v-btn block color="success" :disabled="!(storeId && date)" @click="onSubmit">
                        Получить отчет
                    </v-btn>
                </v-col>
            </v-row>
        </t-card-page>
    </div>
</template>

<script>
import {mapGetters} from 'vuex';
import moment from 'moment/moment';
import months from '@/common/enums/months.ru';
import axiosClient from '@/utils/axiosClient';

export default {
    beforeMount() {
        if (!this.IS_SUPERUSER) {
            this.$toast.error('Доступ запрещен!');
            return this.$router.push('/');
        }
    },
    data: () => ({
        storeId: null,
        date: null,
    }),
    computed: {
        monthsList() {
            const dateStart = moment().add(1, 'month');
            return new Array(12)
                .fill({})
                .map(_ => {
                    return {
                        value: dateStart.subtract(1, 'month').format('YYYY-MM-DD'),
                        name: `${months[+dateStart.get('month')]}, ${dateStart.get('year')}`
                    };
                });
        },
    },
    methods: {
        async onSubmit () {
            const payload = {
                store_id: this.storeId,
                date: this.date,
            };

            await axiosClient.get(`/v2/accounting/shifts?${new URLSearchParams(payload)}`)
        }
    }
}
</script>

<style scoped lang="scss">

</style>
