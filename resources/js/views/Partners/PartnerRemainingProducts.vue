<template>
    <div>
        <t-card-page title="Остатки по складам">
            <v-checkbox
                label="Скрывать отсутствующие"
                v-model="hideNotInStock"
            />
            <v-data-table
                :search="searchQuery"
                no-results-text="Нет результатов"
                no-data-text="Нет данных"
                :headers="headers"
                :page.sync="pagination.page"
                :items="products"
                @page-count="pageCount = $event"
                :items-per-page="10"
                :footer-props="{
                            'items-per-page-options': [10, 15, {text: 'Все', value: -1}],
                            'items-per-page-text': 'Записей на странице',
                        }">
                <template v-slot:item.product_name="{item}">
                    <v-list flat>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ item.product_name }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    <span v-if="item.attributes.length">{{ item.attributes.map(a => a.attribute_value).join(', ') }}, </span>
                                    <span>{{ item.manufacturer.manufacturer_name }}</span>
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </template>
                <template v-slot:item.product_price="{ item }">
                    <v-expansion-panels v-if="storeFilter === -1" accordion flat style="width: 300px;">
                        <v-expansion-panel>
                            <v-expansion-panel-header>
                                Стоимость по городам
                            </v-expansion-panel-header>
                            <v-expansion-panel-content>
                                <v-list>
                                    <v-list-item v-for="store of stores">
                                        <v-list-item-content>
                                            <v-list-item-title>{{ getPrice(item, store.id) | priceFilters}}</v-list-item-title>
                                            <v-list-item-title
                                                class="font-weight-black">{{ store.name }}</v-list-item-title>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                    <span v-else>
                                {{ getPrice(item) | priceFilters }}
                            </span>
                </template>
                <template v-slot:item.category="{ item }">
                    <span>{{ item.category.category_name }}</span>
                </template>
                <template v-slot:item.manufacturer="{ item }">
                    <span>{{ item.manufacturer.manufacturer_name }}</span>
                </template>
                <template v-slot:item.additional_data="{ item }">
                    <v-list>
                        <v-list-item v-if="item.product_name_web" v-show="false">
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ item.product_name_web }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    Название интернет-магазина
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>
                                    <v-icon color="success" v-if="item.is_kaspi_visible">mdi-check</v-icon>
                                    <v-icon color="error" v-else>mdi-close</v-icon>
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    Виден в каспи
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>
                                    <v-icon color="success" v-if="item.is_iherb">mdi-check</v-icon>
                                    <v-icon color="error" v-else>mdi-close</v-icon>
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    Товар IHerb
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </template>
                <template v-slot:item.quantity="{item}">
                    <div v-if="true">
                        <v-list v-if="quantities[item.id]">
                            <v-list-item v-for="(quantity) of getQuantities(item)">
                                <v-list-item-content>
                                    <v-list-item-title>{{ quantity.quantity }} шт</v-list-item-title>
                                    <v-list-item-title
                                        class="font-weight-black">{{ quantity.name }}</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                        <v-list v-else>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>0 шт</v-list-item-title>
                                    <v-list-item-title class="font-weight-black">Всего</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                    </div>
                    <span v-else>
                                {{ item.quantity }} шт.
                            </span>
                </template>
                <template v-slot:item.purchase_price="{item}">
                    <div v-if="true">
                        <v-list v-if="quantities[item.id]">
                            <v-list-item v-for="(quantity) of getPurchasePrices(item)">
                                <v-list-item-content>
                                    <v-list-item-title>{{ quantity.purchase_price | priceFilters }}</v-list-item-title>
                                    <v-list-item-title
                                        class="font-weight-black">{{ quantity.name }}</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                        <v-list v-else>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{ 0 | priceFilters }}</v-list-item-title>
                                    <v-list-item-title class="font-weight-black">Всего</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                    </div>
                    <span v-else>
                                {{ (item.purchase_price || 0) | priceFilters }}
                            </span>
                </template>
                <template slot="footer.page-text" slot-scope="{pageStart, pageStop, itemsLength}">
                    {{ pageStart }}-{{ pageStop }} из {{ itemsLength }}
                </template>
            </v-data-table>
            <div class="text-xs-center pt-2">
                <v-pagination
                    v-model="pagination.page"
                    :total-visible="10"
                    :length="pageCount"></v-pagination>
            </div>
        </t-card-page>
    </div>
</template>

<script>
import ACTIONS from '@/store/actions';
import product from '@/mixins/product';
import product_search from '@/mixins/product_search';

export default {
    mixins: [product, product_search],
    async created () {
        this.$loading.enable('Идет загрузка...');
        //this.storeFilter = -1;
        await this.$store.dispatch('GET_PRODUCTS_v2');
        await this.$store.dispatch(ACTIONS.GET_CATEGORIES);
        // await this.$store.dispatch(ACTIONS.GET_MANUFACTURERS);
        // await this.$store.dispatch(ACTIONS.GET_ATTRIBUTES);
        // await this.$store.dispatch(ACTIONS.GET_SUPPLIERS);
        // await this.getProductQuantities(-1);
        this.$loading.disable();
    },
    data: () => ({
        storeFilter: -1,
        pagination: {
            ascending: true,
            rowsPerPage: 10,
            page: 1
        },
        hideNotInStock: false,
        pageCount: 1,
        showMainProducts: false,
        headers: [
            {
                value: 'product_name',
                text: 'Наименование',
                sortable: true,
            },
           /* {
                value: 'product_price',
                text: 'Стоимость'
            },*/
            {
                value: 'product_barcode',
                text: 'Штрих-код',
                align: ' d-none'
            },
            {
                value: 'manufacturer',
                text: 'Производитель'
            },
            {
                value: 'quantity',
                text: 'Остаток'
            },
            {
                value: 'manufacturer.manufacturer_name',
                text: 'Название производителя',
                align: ' d-none'
            },
            {
                value: 'category',
                text: 'Категория'
            },
        ]
    }),
    computed: {
        quantities() {
            return this.$store.getters.QUANTITIES_v2;
        },
        stores () {
            let stores = this.$store.getters.stores;
            if (this.$user.stores.length > 0) {
                stores = stores.filter(s => {
                    return this.$user.stores.findIndex(st => st.id === s.id) !== -1;
                })
            }
            return stores;
        },
        products() {
            let products = this.showMainProducts ? this.$store.getters.MAIN_PRODUCTS_v2 : this.$store.getters.PRODUCTS_v2;
            if (this.hideNotInStock) {
                if (this.storeFilter !== -1) {
                    products = products.filter(product => product.quantity > 0);
                } else {
                    products = products.filter(product => {
                        const qnts = this.quantities[product.id];
                        if (!qnts) {
                            return false;
                        }
                        const total = qnts.reduce((a, c) => {
                            return a + c.quantity;
                        }, 0);
                        return total > 0;
                    })
                }
            }
/*
            if (this.manufacturerId !== -1) {
                products = products.filter(p => p.manufacturer.id === this.manufacturerId);
            }

            if (this.categoryId !== -1) {
                products = products.filter(p => p.category.id === this.categoryId);
            }

            if (this.subcategoryId !== -1) {
                products = products.filter(p => p.subcategory_id === this.subcategoryId);
            }

            if (this.currentMarginType !== -1) {
                products = products.filter(p => p.margin_type.id === this.currentMarginType);
            }

            if (this.isKaspiVisibleFilter !== -1) {
                products = products.filter(p => p.is_kaspi_visible === this.isKaspiVisibleFilter);
            }*/

            return products;
        },
        headersZZZ() {
            let headers = [
                {
                    value: 'product_name',
                    text: 'Наименование',
                    sortable: true,
                },
                {
                    value: 'product_price',
                    text: 'Стоимость'
                },
                {
                    value: 'product_barcode',
                    text: 'Штрих-код',
                    align: ' d-none'
                },
                {
                    value: 'manufacturer',
                    text: 'Производитель'
                },
                {
                    value: 'manufacturer.manufacturer_name',
                    text: 'Название производителя',
                    align: ' d-none'
                },
                {
                    value: 'category',
                    text: 'Категория'
                },
            ];

            if (this.IS_SUPERUSER || this.IS_SENIOR_SELLER || this.IS_MODERATOR || this.IS_FRANCHISE) {
                headers.unshift({
                    value: 'id',
                    text: 'Артикул',
                    sortable: true
                })
            }

            if (this.IS_SUPERUSER || this.IS_FRANCHISE) {
                headers.splice(3, 0,  {
                    value: 'quantity',
                    text: 'Остаток'
                });
                headers.splice(4, 0,  {
                    value: 'purchase_price',
                    text: 'Общая закупочная стоимость'
                });
            }

            if (this.IS_MODERATOR) {
                headers = headers.filter(h => !['quantity', 'additional_data'].includes(h.value));
            }

            if (!this.IS_SUPERUSER) {
                headers = headers.filter(h => h.value !== 'product_name_web');
            }

            return headers;
        },
    },
    methods: {
        getQuantities(product) {
            if (!this.showMainProducts) {
                return this.quantities[product.id];
            } else {
                const quantities = [];
                product.product_ids.forEach(id => {
                    const needle = this.quantities[id];
                    if (needle) {
                        needle.forEach(qnt => {
                            const idx = quantities.findIndex(q => q.store_id === qnt.store_id);
                            if (idx === -1) {
                                quantities.push(qnt);
                            } else {
                                quantities.splice(idx, 1, {
                                    ...quantities[idx],
                                    quantity: quantities[idx].quantity + qnt.quantity,
                                })
                            }
                        })
                    }
                })
                return quantities;
            }
        },
    },
   /* watch: {
        storeFilter: {
            handler: async function (value) {
                if (value !== null) {

                }
            },
            immediate: true
        }
    }*/
}
</script>

<style scoped lang="scss">

</style>
