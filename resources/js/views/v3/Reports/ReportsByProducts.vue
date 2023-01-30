<template>
    <div>
        <t-card-page title="Отчет по продажам товаров">
            <v-row>
                <v-col cols="12" xl="3" justify="center">
                    <v-list>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title class="font-weight-black">Общая сумма продаж:</v-list-item-title>
                                <v-list-item-title>{{ totalSales | priceFilters }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item v-if="IS_BOSS || IS_FRANCHISE || IS_ACCOUNTING">
                            <v-list-item-content>
                                <v-list-item-title class="font-weight-black">Общая сумма прибыли:</v-list-item-title>
                                <v-list-item-title>{{ totalMargin | priceFilters }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item v-if="IS_BOSS || IS_FRANCHISE || IS_ACCOUNTING">
                            <v-list-item-content>
                                <v-list-item-title class="font-weight-black">Общая себестоимость реализаций:</v-list-item-title>
                                <v-list-item-title>{{ totalSelfPrice | priceFilters }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-col>
                <v-col cols="12" xl="3">
                    <v-select
                        :items="dateFilters"
                        item-text="name"
                        item-value="value"
                        v-model="currentDate"
                        label="Время:"
                        @change="loadReport"
                    />
                </v-col>
                <v-col v-if="currentDate === 4">
                    <label>Произвольная дата</label>
                    <v-menu
                        ref="startMenu"
                        v-model="startMenu"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        :return-value.sync="start"
                        transition="scale-transition"
                        min-width="290px"
                        offset-y
                        full-width
                    >
                        <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="start"
                                label="Дата начала"
                                prepend-icon="event"
                                readonly
                                v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="start"
                            locale="ru"
                            no-title
                            scrollable
                        >
                            <div class="flex-grow-1"></div>
                            <v-btn
                                text
                                outlined
                                color="primary"
                                @click="startMenu = false"
                            >
                                Отмена
                            </v-btn>
                            <v-btn
                                text
                                outlined
                                color="primary"
                                @click="changeCustomDate(startMenu, start)"
                            >
                                OK
                            </v-btn>
                        </v-date-picker>
                    </v-menu>
                    <v-menu
                        ref="finishMenu"
                        v-model="finishMenu"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        :return-value.sync="finish"
                        transition="scale-transition"
                        min-width="290px"
                        offset-y
                        full-width
                    >
                        <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="finish"
                                label="Дата окончания"
                                prepend-icon="event"
                                readonly
                                v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="finish"
                            locale="ru"
                            no-title
                            scrollable
                        >
                            <div class="flex-grow-1"></div>
                            <v-btn
                                text
                                outlined
                                color="primary"
                                @click="finishMenu = false"
                            >
                                Отмена
                            </v-btn>
                            <v-btn
                                text
                                outlined
                                color="primary"
                                @click="changeCustomDate(finishMenu, finish) "
                            >
                                OK
                            </v-btn>
                        </v-date-picker>
                    </v-menu>
                </v-col>
                <v-col>
                    <label>Прочие фильтры:</label>
                    <v-select
                        v-if="IS_SUPERUSER"
                        :items="shops"
                        item-text="name"
                        item-value="id"
                        v-model="currentCity"
                        label="Город:"
                    >
                    </v-select>
                    <v-select
                        v-if="IS_SUPERUSER"
                        :items="sellers"
                        label="Продавец:"
                        v-model="currentSeller"
                        item-value="id"
                        item-text="name">
                    </v-select>
                    <v-autocomplete
                        :items="manufacturers"
                        item-text="manufacturer_name"
                        v-model="manufacturerId"
                        item-value="id"
                        label="Бренд"
                    />
                    <v-autocomplete
                        label="Категория"
                        :items="categories"
                        v-model="categoryId"
                        item-value="id"
                        item-text="name"
                    />
                    <v-autocomplete
                        label="Подкатегория"
                        :items="subcategories"
                        v-model="subcategoryId"
                        item-value="id"
                        item-text="subcategory_name"
                    />
                    <v-checkbox
                        label="Показать только главные товары"
                        v-model="showMainProducts"
                    />
                </v-col>
            </v-row>
            <v-text-field
                label="Поиск по отчету"
                append-icon="search"
                v-model="search"
                clearable
            />
            <v-data-table
                :headers="headers"
                :items="filteredReports"
                :search="search"
            >
                <template v-slot:item.total_purchase_price="{ item }">
                    {{ item.total_purchase_price | priceFilters }}
                </template>
                <template v-slot:item.total_product_price="{ item }">
                    {{ item.total_product_price | priceFilters }}
                </template>
                <template v-slot:item.total_margin="{ item }">
                    {{ item.total_margin | priceFilters }}
                </template>
            </v-data-table>
        </t-card-page>
    </div>
</template>

<script>
import moment from 'moment/moment';
import ACTIONS from '@/store/actions';
import axiosClient from '@/utils/axiosClient';
const DATE_FILTERS = {
    ALL_TIME: 1,
    CURRENT_MONTH: 2,
    TODAY: 3,
    CUSTOM_FILTER: 4,
    LAST_3_DAYS: 5,
};

const DATE_FORMAT = 'YYYY-MM-DD';
export default {
    data: () => ({
        search: '',
        dateFilters: [
            {
                name: 'Сегодня',
                value: [
                    moment().format(DATE_FORMAT),
                    moment().format(DATE_FORMAT),
                ],
            },
            {
                name: 'Вчера',
                value: [
                    moment().subtract(1, 'days').format(DATE_FORMAT),
                    moment().subtract(1, 'days').format(DATE_FORMAT),
                ],
            },
            {
                name: 'Последние 3 дня',
                value: [
                    moment().subtract(3, 'days').format(DATE_FORMAT),
                    moment().format(DATE_FORMAT),
                ],
            },
            {
                name: 'За текущий месяц',
                value: [
                    moment().startOf('month').format(DATE_FORMAT),
                    moment().format(DATE_FORMAT),
                ],
            },
            {
                name: 'За все время',
                value: [
                    moment.unix(1).format(DATE_FORMAT),
                    moment().format(DATE_FORMAT)
                ],
            },
            {
                name: 'Произвольно',
                value: DATE_FILTERS.CUSTOM_FILTER
            },
        ],
        currentDate:  [
            moment().format(DATE_FORMAT),
            moment().format(DATE_FORMAT),
        ],
        today: moment(),
        startMenu: null,
        start: null,
        finishMenu: null,
        finish: null,
        currentCity: -1,
        currentSeller: -1,
        manufacturerId: -1,
        categoryId: -1,
        subcategoryId: -1,
        report: [],
        reportBySku: [],
        reportByProduct: [],
        showMainProducts: false,
        headers: [
            {
                value: 'product_name_full',
                text: 'Наименование',
                sortable: true,
            },
            {
                value: 'total_purchase_price',
                text: 'Общая закупочная стоимость'
            },
            {
                value: 'total_product_price',
                text: 'Общая продажная стоимость'
            },
            {
                value: 'total_margin',
                text: 'Общая маржа'
            },
        ]
    }),
    computed: {
        totalSales () {
            return this.filteredReports.reduce((a, c) => {
                return a + c.total_product_price;
            }, 0);
        },
        totalMargin () {
            return this.filteredReports.reduce((a, c) => {
                return a + c.total_margin;
            }, 0);
        },
        totalSelfPrice () {
            return this.filteredReports.reduce((a, c) => {
                return a + c.total_purchase_price;
            }, 0);
        },
        shops() {
            return [{id: -1, name: 'Все'}, ...this.$store.getters.shops];
        },
        sellers() {
            return [{id: -1, name: 'Все'}, ...this.$store.getters.users];
        },
        manufacturers() {
            return [
                {
                    id: -1,
                    manufacturer_name: 'Все'
                }, ...this.$store.getters.manufacturers];
        },
        categories() {
            return [{
                id: -1,
                name: 'Все'
            }, ...this.$store.getters.categories];
        },
        subcategories() {
            return [
                {
                    id: -1,
                    subcategory_name: 'Все'
                }, ...this.categories
                    .find(c => c.id === this.categoryId)
                    .subcategories || []];
        },
        filteredReports () {
            const reports = this.showMainProducts ? this.reportByProduct : this.reportBySku;
            return reports
                .filter(r => {
                    return this.manufacturerId === -1 ? true : r.manufacturer_id === this.manufacturerId;
                })
                .filter(r => {
                    return this.categoryId === -1 ? true : r.category_id === this.categoryId;
                })
                .filter(r => {
                    return this.subcategoryId === -1 ? true : r.subcategory_id === this.subcategoryId;
                })
        },
    },
    async created () {
        this.$loading.enable();
        await this.loadReport();
        await this.$store.dispatch(ACTIONS.GET_CATEGORIES);
        await this.$store.dispatch(ACTIONS.GET_MANUFACTURERS);
        await this.$store.dispatch(ACTIONS.GET_ATTRIBUTES);
        await this.$store.dispatch(ACTIONS.GET_SUPPLIERS);
        this.$loading.disable();
    },
    watch: {
        currentCity () {
            this.loadReport();
        },
        currentSeller () {
            this.loadReport();
        },
    },
    methods: {
        async loadReport () {
            if (this.currentDate === DATE_FILTERS.CUSTOM_FILTER) {
                if (!(this.start || this.finish)) {
                    return;
                }
            }
            const payload = {
                start: this.currentDate === DATE_FILTERS.CUSTOM_FILTER ? this.start : this.currentDate[0],
                finish: this.currentDate === DATE_FILTERS.CUSTOM_FILTER ? this.finish : this.currentDate[1],
            };

            if (this.currentSeller !== -1) {
                payload.seller_id = this.currentSeller;
            }

            if (this.currentCity !== -1) {
                payload.store_id = this.currentCity;
            }

            const params = new URLSearchParams(payload);

            try {
                this.$loading.enable();
                const { data } = await axiosClient.get(`v2/reports/products?${params}`);
                this.reportBySku = data.report_by_sku;
                this.reportByProduct = data.report_by_products;
            } catch (e) {
                this.$toast.error('Произошла ошибка!');
            } finally {
                this.$loading.disable();
            }

        },
        async changeCustomDate() {
            this.$refs.startMenu.save(this.start);
            this.$refs.finishMenu.save(this.finish);

            if (this.start && this.finish) {
                await this.loadReport();
            }
        },
    }
}
</script>

<style scoped lang="scss">

</style>
