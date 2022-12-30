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
            <div v-if="report" style="overflow-x: scroll;">
                <table class="my-4" style="overflow-x: scroll">
                    <tr>
                        <th rowspan="3" class="orange-cell">
                            <span class="rotated">Число</span>
                        </th>
                        <th rowspan="2" colspan="3" class="orange-cell">
                            <span class="rotated">Переход с пред дня</span>
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
                        <th rowspan="1" colspan="1" class="orange-cell">
                            Отчет
                        </th>
                        <th rowspan="1" colspan="1" class="orange-cell">
                            Факт
                        </th>
                        <th rowspan="1" colspan="1" class="orange-cell">
                            Разница
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
                            {{ item.report.prev_day_cash_in_hand.report | priceFilters }}
                        </td>
                        <td class="orange-cell">
                            {{ item.report.prev_day_cash_in_hand.fact | priceFilters }}
                        </td>
                        <td
                            class="orange-cell"
                            :class="[
                                {'red-text': item.report.prev_day_cash_in_hand.diff !== 0},
                                {'green-text': item.report.prev_day_cash_in_hand.diff === 0},
                            ]"
                        >
                            {{ item.report.prev_day_cash_in_hand.diff | priceFilters }}
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
                    <tr>
                        <td class="orange-cell">
                            Итого
                        </td>
                        <td class="orange-cell">-</td>
                        <td class="orange-cell">-</td>
                        <td class="orange-cell"
                            :class="[
                                {'red-text': totalCashInHandDiff !== 0},
                                {'green-text': totalCashInHandDiff === 0},
                            ]"
                        >
                            {{ totalCashInHandDiff | priceFilters }}
                        </td>
                        <td class="orange-cell">
                            {{ totalSales | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashSales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashSales.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashSales.diff | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalKaspiSales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalKaspiSales.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalKaspiSales.diff | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalJysanSales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalJysanSales.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalJysanSales.diff | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalSalesObject.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalSalesObject.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalSalesObject.diff | priceFilters }}
                        </td>
                        <td class="red-cell">-</td>
                        <td class="orange-cell">-</td>
                    </tr>
                </table>
            </div>
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
        totalCashInHandDiff () {
            return this.report.reduce((a, c) => {
                return a + c.report.prev_day_cash_in_hand.diff;
            }, 0)
        },
        totalSales () {
            return this.report.reduce((a, c) => {
                return a + c.report.total_sales.by_crm;
            }, 0);
        },
        totalCashSales () {
            return {
                by_crm: this.report.reduce((a, c) => {
                    return a + c.report.cash_sales.by_crm
                }, 0),
                by_shift: this.report.reduce((a, c) => {
                    return a + c.report.cash_sales.by_shift;
                }, 0),
                diff: this.report.reduce((a, c) => {
                    return a + c.report.cash_sales.diff;
                }, 0),
            };
        },
        totalKaspiSales () {
            return {
                by_crm: this.report.reduce((a, c) => {
                    return a + c.report.kaspi_sales.by_crm
                }, 0),
                by_shift: this.report.reduce((a, c) => {
                    return a + c.report.kaspi_sales.by_shift;
                }, 0),
                diff: this.report.reduce((a, c) => {
                    return a + c.report.kaspi_sales.diff;
                }, 0),
            };
        },
        totalJysanSales () {
            return {
                by_crm: this.report.reduce((a, c) => {
                    return a + c.report.jysan_sales.by_crm
                }, 0),
                by_shift: this.report.reduce((a, c) => {
                    return a + c.report.jysan_sales.by_shift;
                }, 0),
                diff: this.report.reduce((a, c) => {
                    return a + c.report.jysan_sales.diff;
                }, 0),
            };
        },
        totalSalesObject () {
            return {
                by_crm: this.report.reduce((a, c) => {
                    return a + c.report.total_sales.by_crm
                }, 0),
                by_shift: this.report.reduce((a, c) => {
                    return a + c.report.total_sales.by_shift;
                }, 0),
                diff: this.report.reduce((a, c) => {
                    return a + c.report.total_sales.diff;
                }, 0),
            };
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

.green-text {
    color: green!important;;
}

</style>
