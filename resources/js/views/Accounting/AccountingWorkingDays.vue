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
            <div v-if="report" style="width: 2000px;">
                <table class="my-4 w-full">
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
                        <th rowspan="1" colspan="14" scope="colgroup" class="green-cell">Поступление</th>
                        <th colspan="4" rowspan="1" class="red-cell">Изъятие</th>
                        <th colspan="1" rowspan="3" class="orange-cell">Переход на след. день</th>
                    </tr>
                    <tr>
                        <th rowspan="1" colspan="3" class="green-cell">
                            Наличные
                        </th>
                        <th rowspan="1" colspan="3" class="green-cell">
                            Переводы
                        </th>
                        <th rowspan="1" colspan="3" class="green-cell">
                            Безналичный
                        </th>
                        <th rowspan="1" colspan="2" class="green-cell">
                            Прочие поступления
                        </th>
                        <th rowspan="1" colspan="3" class="green-cell">
                            Итого
                        </th>
                        <th rowspan="2" colspan="1" class="red-cell">
                            Инкассация
                        </th>
                        <th rowspan="1" colspan="2" class="red-cell">
                            Расходы
                        </th>
                        <th rowspan="2" colspan="1" class="red-cell">
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
                            Наличный
                        </th>
                        <th rowspan="1" colspan="1" class="green-cell">
                            Безналичный
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
                        <th rowspan="1" colspan="1" class="red-cell">
                            Наличный
                        </th>
                        <th rowspan="1" colspan="1" class="red-cell">
                            Безналичный
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
                            {{ item.report.cashless_sales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ item.report.cashless_sales.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell red-text">
                        <span>
                            {{ item.report.cashless_sales.diff | priceFilters }}
                        </span>
                        </td>
                        <td class="green-cell">
                            {{ item.report.other_checkins.cash | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ item.report.other_checkins.cashless | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ item.report.total_sales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ getTotalByShift(item.report) | priceFilters }}
                        </td>
                        <td class="green-cell red-text">
                        <span>
                            {{ (getTotalByShift(item.report) - item.report.total_sales.by_crm) | priceFilters }}
                        </span>
                        </td>
                        <td class="red-cell">
                            {{ item.report.with_drawals.incassation | priceFilters }}
                        </td>
                        <td class="red-cell">
                            <v-tooltip right v-if="item.report.with_drawals.cash.by_types.length > 0">
                                <template v-slot:activator="{ on, attrs }">
                                    <div
                                        class="d-flex align-center justify-center"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        <span>
                                            {{ item.report.with_drawals.cash.total_without_inc | priceFilters }}
                                        </span>
                                        <v-icon>
                                            mdi-information-outline
                                        </v-icon>
                                    </div>
                                </template>
                                <ul>
                                    <li v-for="(item) of item.report.with_drawals.cash.by_types">
                                        {{ item.name }}: {{ item.amount | priceFilters }}
                                    </li>
                                </ul>
                            </v-tooltip>
                            <span v-else>
                                {{ item.report.with_drawals.cash.total_without_inc | priceFilters }}
                            </span>
                        </td>
                        <td class="red-cell">
                            <v-tooltip right v-if="item.report.with_drawals.cashless.by_types.length > 0">
                                <template v-slot:activator="{ on, attrs }">
                                    <div
                                        class="d-flex align-center justify-center"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        <span>
                                            {{ item.report.with_drawals.cashless.total_without_inc | priceFilters }}
                                        </span>
                                        <v-icon>
                                            mdi-information-outline
                                        </v-icon>
                                    </div>
                                </template>
                                <ul>
                                    <li v-for="(item) of item.report.with_drawals.cashless.by_types">
                                        {{ item.name }}: {{ item.amount | priceFilters }}
                                    </li>
                                </ul>
                            </v-tooltip>
                            <span v-else>
                                {{ item.report.with_drawals.cashless.total_without_inc | priceFilters }}
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
                            {{ totalCashlessSales.by_crm | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashlessSales.by_shift | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashlessSales.diff | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashIns | priceFilters }}
                        </td>
                        <td class="green-cell">
                            {{ totalCashlessIns | priceFilters }}
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
                        <td class="red-cell">
                            {{ totalIncassation | priceFilters }}
                        </td>
                        <td class="red-cell">
                            <v-tooltip right>
                                <template v-slot:activator="{ on, attrs }">
                                    <div
                                        class="d-flex align-center justify-center"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        {{ totalOtherWithdrawalsByCash | priceFilters }}
                                        <v-icon>
                                            mdi-information-outline
                                        </v-icon>
                                    </div>
                                </template>
                                <ul>
                                <li v-for="(item) of totalWithDrawalByTypesByCash">
                                        {{ item.name }}: {{ item.amount | priceFilters }}
                                    </li>
                                </ul>
                            </v-tooltip>
                        </td>
                        <td class="red-cell">
                            <v-tooltip right>
                                <template v-slot:activator="{ on, attrs }">
                                    <div
                                        class="d-flex align-center justify-center"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        {{ totalOtherWithdrawalsByCashless | priceFilters }}
                                        <v-icon>
                                            mdi-information-outline
                                        </v-icon>
                                    </div>
                                </template>
                                <ul>
                                    <li v-for="(item) of totalWithDrawalByTypesByCashless">
                                        {{ item.name }}: {{ item.amount | priceFilters }}
                                    </li>
                                </ul>
                            </v-tooltip>
                        </td>
                        <td class="red-cell">
                            {{ totalWithdrawals | priceFilters }}
                        </td>
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
        if (!this.IS_SUPERUSER && !this.IS_ACCOUNTING) {
            this.$toast.error('Доступ запрещен!');
            return this.$router.push('/');
        }
    },
    data: () => ({
        storeId: null,
        date: null,
        report: null,
        withdrawal_types: [],
    }),
    computed: {
        totalCashIns () {
            return this.report.reduce((a, c) => {
                return a + c.report.other_checkins.cash;
            }, 0)
        },
        totalCashlessIns () {
            return this.report.reduce((a, c) => {
                return a + c.report.other_checkins.cashless;
            }, 0)
        },
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
        totalWithDrawalByTypesByCash() {
            let output = [];
            this.withdrawal_types.forEach((type) => {
                const amount = this.report.reduce((a, c) => {
                    let amount = 0;
                    const needleWithdrawal = c.report.with_drawals.cash.by_types.find(w => w.id === type.id);
                    if (needleWithdrawal) {
                        amount = needleWithdrawal.amount;
                    }
                    return a + amount;
                }, 0);
                output.push({
                    name: type.name,
                    id: type.id,
                    amount,
                })
            });
            return output;
        },
        totalWithDrawalByTypesByCashless() {
            let output = [];
            this.withdrawal_types.forEach((type) => {
                const amount = this.report.reduce((a, c) => {
                    let amount = 0;
                    const needleWithdrawal = c.report.with_drawals.cashless.by_types.find(w => w.id === type.id);
                    if (needleWithdrawal) {
                        amount = needleWithdrawal.amount;
                    }
                    return a + amount;
                }, 0);
                output.push({
                    name: type.name,
                    id: type.id,
                    amount,
                })
            });
            return output;
        },
        totalWithdrawals() {
            return this.report.reduce((a, c) => {
                return a + c.report.with_drawals.total;
            }, 0)
        },
        totalIncassation() {
            return this.report.reduce((a, c) => {
                return a + c.report.with_drawals.incassation;
            }, 0)
        },
        totalOtherWithdrawalsByCash() {
            return this.report.reduce((a, c) => {
                return a + c.report.with_drawals.cash.total_without_inc;
            }, 0)
        },
        totalOtherWithdrawalsByCashless() {
            return this.report.reduce((a, c) => {
                return a + c.report.with_drawals.cashless.total_without_inc;
            }, 0)
        },
        totalCashInHandDiff() {
            return this.report.reduce((a, c) => {
                return a + c.report.prev_day_cash_in_hand.diff;
            }, 0)
        },
        totalSales() {
            return this.report.reduce((a, c) => {
                return a + c.report.total_sales.by_crm;
            }, 0);
        },
        totalCashSales() {
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
        totalKaspiSales() {
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
        totalCashlessSales() {
            return {
                by_crm: this.report.reduce((a, c) => {
                    return a + c.report.cashless_sales.by_crm
                }, 0),
                by_shift: this.report.reduce((a, c) => {
                    return a + c.report.cashless_sales.by_shift;
                }, 0),
                diff: this.report.reduce((a, c) => {
                    return a + c.report.cashless_sales.diff;
                }, 0),
            };
        },
        totalSalesObject() {
            let byCrm = this.report.reduce((a, c) => {
                return a + c.report.total_sales.by_crm
            }, 0);
            let byShift = this.report.reduce((a, c) => {
                return a + this.getTotalByShift(c.report)/*c.report.total_sales.by_shift*/;
            }, 0);
            return {
                by_crm: byCrm,
                by_shift: byShift,
                diff: byShift - byCrm
            };
        },
    },
    methods: {
        getTotalByShift (report) {
            return report.cash_sales.by_shift + report.kaspi_sales.by_shift + report.cashless_sales.by_shift;
        },
        getCashSalesByShift (report) {
            return report.closing_day_cash_in_hand
            - report.prev_day_cash_in_hand.fact
            - report.other_checkins.cash
            + report.with_drawals.incassation
            + report.with_drawals.cash.total_without_inc
            ;
        },
        async onSubmit() {
            try {
                this.$loading.enable();
                const payload = {
                    store_id: this.storeId,
                    date: this.date,
                };
                this.report = null;
                const {data} = await axiosClient.get(`/v2/accounting/shifts?${new URLSearchParams(payload)}`)
                this.report = data.report;
                this.withdrawal_types = data.withdrawal_types;
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
    font-weight: 500 !important;
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
    color: red !important;;
}

.green-text {
    color: green !important;;
}

</style>
