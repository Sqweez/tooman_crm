<template>
    <v-card>
        <v-card-title>Все товары</v-card-title>
        <v-card-text v-if="!loading">
            <v-row>
                <v-col>
                    <v-row>
                        <v-col cols="12" xl="6">
                            <v-text-field
                                class="mt-2"
                                v-on:input="searchInput"
                                v-model="searchValue"
                                solo
                                clearable
                                label="Поиск товара"
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xl="4" v-show="IS_SUPERUSER">
                            <v-select
                                :items="stores"
                                item-text="name"
                                v-model="storeFilter"
                                item-value="id"
                                label="Склад"
                                :disabled="!IS_SUPERUSER"
                            />
                        </v-col>
                        <v-col cols="12" xl="2" v-if="IS_SUPERUSER">
                            <v-checkbox
                                label="Скрывать отсутствующие"
                                v-model="hideNotInStock"
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12" xl="4">
                            <v-autocomplete
                                label="Производитель"
                                :items="manufacturers"
                                v-model="manufacturerId"
                                item-value="id"
                                item-text="manufacturer_name"
                            />
                        </v-col>
                        <v-col cols="12" xl="4">
                            <v-autocomplete
                                label="Категория"
                                :items="categories"
                                v-model="categoryId"
                                item-value="id"
                                item-text="name"
                            />
                        </v-col>
                        <v-col cols="12" xl="4">
                            <v-autocomplete
                                label="Подкатегория"
                                :items="subcategories"
                                v-model="subcategoryId"
                                item-value="id"
                                item-text="subcategory_name"
                            />
                        </v-col>
                        <v-col cols="12" xl="4">
                            <v-checkbox
                                label="Показать только главные товары"
                                v-model="showMainProducts"
                            />
                        </v-col>
                    </v-row>
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
                                            {{ item.attributes.map(a => a.attribute_value).join(', ') }}, {{ item.manufacturer.manufacturer_name }}
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
                            <div v-if="storeFilter === -1">
                                <v-list v-if="quantities[item.id]">
                                    <v-list-item v-for="(quantity) of getQuantities(item.id)">
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
                            <div v-if="storeFilter === -1">
                                <v-list v-if="quantities[item.id]">
                                    <v-list-item v-for="(quantity) of getPurchasePrices(item.id)">
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
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script>
import ProductRangeModal from "@/components/Modal/ProductRangeModal";
import ProductModal from "@/components/v2/Modal/ProductModal";
import ConfirmationModal from "@/components/Modal/ConfirmationModal";
import ProductQuantityModal from "@/components/Modal/ProductQuantityModal";
import ACTIONS from "@/store/actions";
import axios from 'axios';
import PriceTagModal from "@/components/Modal/PriceTagModal";
import product from "@/mixins/product";
import product_search from "@/mixins/product_search";
import {PRODUCT_MODAL_EVENTS} from "@/config/consts";
import SkuModal from "@/components/v2/Modal/SkuModal";
import _ from 'lodash';

export default {
    async created() {
        this.showMainProducts = !!this.IS_MODERATOR;
        const store_id = (this.is_admin || this.IS_BOSS) ? null : this.user.store_id;
        console.log(store_id);
        try {
            await this.$store.dispatch('GET_PRODUCTS_v2');
        } catch (e) {
            console.log(e.response);
        }
        await this.$store.dispatch(ACTIONS.GET_STORES, store_id);
        if (this.IS_SUPERUSER) {
            this.storeFilter = -1;
        } else {
            this.storeFilter = -1;
        }

        if (this.IS_FRANCHISE) {
            this.storeFilter = this.user.store_id;
        }

        await this.$store.dispatch(ACTIONS.GET_CATEGORIES);
        await this.$store.dispatch(ACTIONS.GET_MANUFACTURERS);
        await this.$store.dispatch(ACTIONS.GET_ATTRIBUTES);
        await this.$store.dispatch(ACTIONS.GET_SUPPLIERS);
    },
    data: () => ({
        priceTagModal: false,
        waitingQuantities: false,
        loading: false,
        options: {},
        productModal: false,
        productRangeModal: false,
        productQuantityModal: false,
        pageCount: 1,
        deleteModal: false,
        productId: null,
        storeFilter: null,
        rangeMode: false,
        priceTag: {},
        pagination: {
            ascending: true,
            rowsPerPage: 10,
            page: 1
        },
        photoFilter: 0,
        photoFilters: [
            {
                name: 'Все товары',
                id: 0,
            },
            {
                name: 'Товары без фото',
                id: 1,
            },
            {
                name: 'Товары с фото',
                id: 2,
            },
        ],
        hideNotInStock: false,
        manufacturerId: -1,
        categoryId: -1,
        subcategoryId: -1,
        currentMarginType: -1,
        kaspiVisibleFilters: [
            {
                id: -1,
                text: 'Не важно'
            },
            {
                id: true,
                text: 'Да'
            },
            {
                id: false,
                text: 'Нет'
            },
        ],
        isKaspiVisibleFilter: -1,
        showMainProducts: false,
    }),
    computed: {
        marginTypes () {
            return [{id: -1, title: 'Все'}, ...this.$store.getters.MARGIN_TYPES];
        },
        manufacturers() {
            return [
                {
                    id: -1,
                    manufacturer_name: 'Все'
                },
                ...this.$store.getters.manufacturers
            ];
        },
        quantities() {
            return this.$store.getters.QUANTITIES_v2;
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
            }

            return products;
        },
        stores() {
            return [{
                name: 'Все',
                id: -1
            }, ...this.$store.getters.stores];
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
        totalProducts() {
            return this.$store.getters.totalProducts;
        },
        user() {
            return this.$store.getters.USER;
        },
        is_admin() {
            return this.$store.getters.IS_ADMIN || this.$store.getters.IS_STOREKEEPER;
        },
        headers() {
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

            if (this.is_admin || this.IS_BOSS || this.IS_SENIOR_SELLER || this.IS_MODERATOR || this.IS_FRANCHISE) {
                headers.unshift({
                    value: 'id',
                    text: 'ID',
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
        async getProductQuantities(value) {
            if (!this.waitingQuantities) {
                const debouncedFn = _.debounce(() => {
                    try {
                        this.$store.dispatch('GET_PRODUCT_QUANTITIES_WITH_PURCHASE', value);
                    } catch (e) {
                        console.log(e.response);
                    }
                    this.waitingQuantities = false;
                }, 150)

                debouncedFn();
            }

            this.waitingQuantities = true;
        },
        getQuantities(id) {
            let qnt = this.quantities[id];
            if (!this.IS_SUPERUSER) {
                qnt = qnt.filter(q => {
                    return [-1, 1, 6, this.user.store_id].includes(q.store_id);
                });
                qnt = qnt.map(q => {
                    if (q.store_id === -1) {
                        q.quantity = qnt.filter(q => q.store_id !== -1).reduce((a, c) => {
                            return a + c.quantity;
                        }, 0)
                    }
                    return q;
                })
            }
            return qnt;
        },
        getPurchasePrices (id) {
            let qnt = this.quantities[id];
            if (!this.IS_SUPERUSER) {
                qnt = qnt.filter(q => {
                    return [-1, 1, 6, this.user.store_id].includes(q.store_id);
                });
                qnt = qnt.map(q => {
                    if (q.store_id === -1) {
                        q.quantity = qnt.filter(q => q.store_id !== -1).reduce((a, c) => {
                            return a + c.quantity;
                        }, 0)
                    }
                    return q;
                })
            }
            return qnt;
        },
        async exportProductBatches() {
            this.loading = true;
            const {data} = await axios.get('/api/v2/documents/batches/purchases');
            const link = document.createElement('a');
            link.href = data.path;
            link.click();
            this.loading = false;
        },
    },
    mixins: [product, product_search],
    watch: {
        storeFilter: {
            handler: async function (value) {
                if (value !== null) {
                    await this.getProductQuantities(value);
                }
            },
            immediate: true
        }
    }
}
</script>

<style>
.actions-products__container {
    display: flex;
    flex-direction: column;
    row-gap: 10px;
    margin-bottom: 10px;
    width: 200px;
}

.v-data-table__mobile-row:first-child > .v-data-table__mobile-row__header {
    display: none;
}

.v-data-table__mobile-row:first-child > .v-data-table__mobile-row__cell {
    width: 100%;
}

.v-data-table__mobile-row .actions-products__container {
    margin-top: 10px;
    width: 100%;
}
</style>
