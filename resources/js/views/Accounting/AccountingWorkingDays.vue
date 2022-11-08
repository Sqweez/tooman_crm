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
            <table class="my-4" v-if="report">
                <tr>
                    <th rowspan="3" class="orange-cell">
                        <span class="rotated">Число</span>
                    </th>
                    <th rowspan="3" class="orange-cell">
                        <span class="rotated">Переход с пред. дня</span>
                    </th>
                    <th rowspan="3" class="orange-cell">
                        <span class="rotated">Продажи</span>
                    </th>
                    <th rowspan="1" colspan="12" scope="colgroup" class="green-cell">Поступление</th>
                    <th colspan="1" rowspan="3" class="red-cell">Изъятие</th>
                    <th colspan="1" rowspan="3" class="orange-cell">Переход на след. день</th>
                </tr>
                <tr>
                   <th rowspan="1" colspan="3" class="green-cell">
                       Наличные
                   </th>
                    <th rowspan="1" colspan="3" class="green-cell">
                        Каспи
                    </th>
                    <th rowspan="1" colspan="3" class="green-cell">
                        Jysan
                    </th>
                    <th rowspan="1" colspan="3" class="green-cell">
                        Итого
                    </th>
                </tr>
                <tr>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По отчету
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По кассе
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        (+ -)
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По отчету
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По кассе
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        (+ -)
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По отчету
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По кассе
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        (+ -)
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По отчету
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        По кассе
                    </th>
                    <th rowspan="1" colspan="1" class="green-cell">
                        (+ -)
                    </th>
                </tr>
                <tr v-for="(item, key) of report" :key="key">
                    <td class="orange-cell">
                        {{ key + 1 }}
                    </td>
                    <td class="orange-cell">
                        {{ item.report.prev_day_cash_in_hand | priceFilters }}
                    </td>
                    <td class="orange-cell">
                        {{ item.report.total_sales.by_crm | priceFilters }}
                    </td>
                    <td class="green-cell">
                        {{ item.report.cash_sales.by_crm | priceFilters }}
                    </td>
                    <td class="green-cell">
                        {{ item.report.cash_sales.by_shift | priceFilters }}
                    </td>
                    <td class="green-cell red-text">
                        <span>
                            {{ item.report.cash_sales.diff | priceFilters }}
                        </span>
                    </td>
                    <td class="green-cell">
                        {{ item.report.kaspi_sales.by_crm | priceFilters }}
                    </td>
                    <td class="green-cell">
                        {{ item.report.kaspi_sales.by_shift | priceFilters }}
                    </td>
                    <td class="green-cell red-text">
                        <span>
                            {{ item.report.kaspi_sales.diff | priceFilters }}
                        </span>
                    </td>
                    <td class="green-cell">
                        {{ item.report.jysan_sales.by_crm | priceFilters }}
                    </td>
                    <td class="green-cell">
                        {{ item.report.jysan_sales.by_shift | priceFilters }}
                    </td>
                    <td class="green-cell red-text">
                        <span>
                            {{ item.report.jysan_sales.diff | priceFilters }}
                        </span>
                    </td>
                    <td class="green-cell">
                        {{ item.report.total_sales.by_crm | priceFilters }}
                    </td>
                    <td class="green-cell">
                        {{ item.report.total_sales.by_shift | priceFilters }}
                    </td>
                    <td class="green-cell red-text">
                        <span>
                            {{ item.report.total_sales.diff | priceFilters }}
                        </span>
                    </td>
                    <td class="red-cell">
                        {{ item.report.with_drawals.total | priceFilters }}
                    </td>
                    <td class="orange-cell">
                        {{ item.report.closing_day_cash_in_hand | priceFilters }}
                    </td>
                </tr>
            </table>
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
        report: null,
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
            try {
                this.$loading.enable();
                const payload = {
                    store_id: this.storeId,
                    date: this.date,
                };
                this.report = null;
                const { data } = await axiosClient.get(`/v2/accounting/shifts?${new URLSearchParams(payload)}`)
                this.report = data.report;
            } catch (e) {
                console.log(e);
                this.$toast.error('Произошла ошибка')
            } finally {
                this.$loading.disable();
            }
        }
    }
}
</script>

<style scoped lang="scss">
.rotated {
    transform-origin: 0 0;
    transform: rotate(-90deg);
}

th, td {
    border: 1px solid gray;
    border-collapse: collapse;
    font-weight: 500!important;
}

td {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.orange-cell {
    background: #FFB74D;
    color: #000;
}

.green-cell {
    background: #9CCC65;
    color: #000;
}

.red-cell {
    background: #E57373;
    color: #000;
}

.red-text {
    color: red!important;;
}

</style>
