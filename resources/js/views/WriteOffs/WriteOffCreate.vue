<template>
    <div>
        <v-card v-if="!emptyCart">
            <v-card-text>
                <v-simple-table v-slot:default class="mt-5">
                    <template>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Наименование</th>
                            <th>Количество</th>
                            <th>Цена</th>
                            <th>Стоимость</th>
                            <th>Удалить</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) of cart" :key="item.id * 85">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <v-list class="product__list" flat tile color="#1e1e1e" dark>
                                    <v-list-item color="#1e1e1e" dark>
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
                            </td>
                            <td class="d-flex align-center">
                                <v-btn icon color="error" @click="decreaseCartCount(index)">
                                    <v-icon>mdi-minus</v-icon>
                                </v-btn>
                                <v-text-field
                                    v-model.number="item.count"
                                    @input="updateCount($event, item)"
                                    @change="updateCount($event, item)"
                                    style="min-width: 40px; max-width: 40px; text-align: center"
                                    type="number"
                                ></v-text-field>
                                <v-btn icon color="success" @click="increaseCartCount(index)">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </td>
                            <td>
                                <v-text-field
                                    v-model.number="item.product_price"
                                />
                            </td>
                            <td>{{ (item.product_price * item.count) | priceFilters }}</td>
                            <td>
                                <v-btn icon color="error" @click="deleteFromCart(index)">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <v-simple-table v-slot:default class="py-4">
                    <template>
                        <thead>
                        <tr>
                            <th class="text-center">Общее количество</th>
                            <th class="text-center">Общая сумма</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-center">{{ cartCount }} шт.</td>
                            <td class="text-center">{{ subtotal }} ₸</td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <div>
                    <v-textarea v-model="description" rows="4" label="Комментарий"></v-textarea>
                    <v-btn color="error" block style="font-size: 16px" @click="createWriteOff">
                        Создать списание
                    </v-btn>
                </div>
            </v-card-text>
        </v-card>
        <v-card>
            <v-card-title>
                Товары
            </v-card-title>
            <v-card-text v-if="loading">
                <div
                    class="text-center d-flex align-center justify-center"
                    style="min-height: 651px">
                    <v-progress-circular
                        indeterminate
                        size="65"
                        color="primary"
                    ></v-progress-circular>
                </div>
            </v-card-text>
            <v-card-text style="padding: 0;" v-if="!loading">
                <v-row>
                    <v-col cols="12" xl="8">
                        <v-text-field
                            class="mt-2"
                            v-model="searchValue"
                            @input="searchInput"
                            solo
                            clearable
                            label="Поиск товара"
                            single-line
                            hide-details
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xl="2">
                        <v-checkbox
                            v-model="hideNotInStock"
                            label="Скрывать отсутствующие"
                        />
                    </v-col>
                    <v-col cols="12" xl="4">
                        <v-autocomplete
                            :items="categories"
                            item-text="name"
                            v-model="categoryId"
                            item-value="id"
                            label="Категория"
                        />
                    </v-col>
                    <v-col cols="12" xl="4">
                        <v-autocomplete
                            :items="manufacturers"
                            item-text="manufacturer_name"
                            v-model="manufacturerId"
                            item-value="id"
                            label="Бренд"
                        />
                    </v-col>
                    <v-col cols="12" xl="4">
                        <v-select
                            :items="stores"
                            item-text="name"
                            v-model="storeFilter"
                            item-value="id"
                            label="Склад"
                            :disabled="!IS_SUPERUSER"
                        />
                    </v-col>
                </v-row>
                <v-data-table
                    class="background-tooman-grey fz-18"
                    :search="searchQuery"
                    no-results-text="Нет результатов"
                    no-data-text="Нет данных"
                    :headers="headers"
                    :items="products"
                    :footer-props="{
                            'items-per-page-options': [10, 15, {text: 'Все', value: -1}],
                            'items-per-page-text': 'Записей на странице',
                        }"
                >
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
                        {{ getPrice(item) | priceFilters}}
                    </template>
                    <template v-slot:item.actions="{item}">
                        <v-btn icon @click="addToCart(item)" color="success">
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </template>
                    <template v-slot:item.quantity="{item}">
                        {{ item.quantity - getCartCount(item.id) }}
                    </template>
                    <template slot="footer.page-text" slot-scope="{pageStart, pageStop, itemsLength}">
                        {{ pageStart }}-{{ pageStop }} из {{ itemsLength }}
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
        <ConfirmationModal
            :state="confirmationModal"
            message="Сформировать накладную?"
            :on-confirm="getWayBill"
            @cancel="cart = []; confirmationModal = false;"
        />
        <WayBillModal
            :state="wayBillModal"
            v-on:cancel="wayBillModal = false"
        />
    </div>
</template>

<script>
import ConfirmationModal from "@/components/Modal/ConfirmationModal";
import WayBillModal from "@/components/Modal/WayBillModal";
import ACTIONS from "@/store/actions";
import axios from 'axios';
import product from "@/mixins/product";
import product_search from "@/mixins/product_search";
import cart from "@/mixins/cart";
import {mapActions} from 'vuex';

export default {
    components: {
        ConfirmationModal,
        WayBillModal
    },
    watch: {
        storeFilter() {
            this.cart = [];
        },
    },
    data: () => ({
        description: '',
        cart: [],
        search: '',
        confirmationModal: false,
        wayBillModal: false,
        child_store: 1,
        overlay: false,
        loading: false,
        photos: [],
    }),
    async mounted() {
        this.$loading.enable();
        await this.$store.dispatch('GET_PRODUCTS_v2');
        this.storeFilter = this.IS_SUPERUSER ? this.stores[0].id : this.$user.store_id;
        await this.$store.dispatch(ACTIONS.GET_MANUFACTURERS);
        await this.$store.dispatch(ACTIONS.GET_CATEGORIES);
        this.$loading.disable();
    },
    mixins: [product, product_search, cart],
    methods: {
        ...mapActions({
            '$createWriteOff': 'createWriteOff',
        }),
        async createWriteOff() {
            const payload = {
                store_id: this.storeFilter,
                revision_id: null,
                description: this.description,
                products: this.cart.map(c => ({ id: c.id, product_price: c.product_price, quantity: c.count }))
            };
            try {
                this.$loading.enable();
                await this.$createWriteOff(payload);
                this.$toast.success('Списание успешно создано!');
                this.cart = [];
                this.description = '';
            } catch (e) {
                console.log(e);
            } finally {
                this.$loading.disable()
            }
        },
        async getWayBill() {
            this.confirmationModal = false;
            const {data} = await axios.post('/api/excel/transfer/waybill', {
                child_store: this.child_store,
                parent_store: this.storeFilter,
                cart: this.cart,
            });

            const link = document.createElement('a');
            link.href = data.path;
            link.click();
            this.cart = [];
        },
    },
    computed: {
        headers () {
            const headers =  [
                {
                    text: 'Наименование',
                    value: 'product_name',
                    sortable: false,
                    align: ' fz-18'
                },
                {
                    text: 'Атрибуты',
                    value: 'attributes',
                    align: ' d-none'
                },
                {
                    value: 'manufacturer.manufacturer_name',
                    text: 'Производитель',
                    align: ' d-none'
                },
                {
                    text: 'Остаток',
                    value: 'quantity'
                },
                {
                    text: 'Стоимость',
                    value: 'product_price'
                },
                {
                    text: 'Добавить',
                    value: 'actions'
                },
                {
                    text: 'Штрих-код',
                    value: 'product_barcode',
                    align: ' d-none'
                }
            ];

            if (!this.IS_SUPERUSER) {
                headers.splice(3, 1);
            }
            return headers;
        },
        IS_SELLER() {
            return this.$store.getters.IS_SELLER;
        },
        IS_ADMIN() {
            return this.$store.getters.IS_ADMIN;
        },
        _stores() {
            const stores = this.stores.filter(s => s.id !== this.storeFilter);
            this.child_store = this.IS_SUPERUSER ? stores[0].id : this.user.store_id;
            return stores;
        },
        products() {
            let products = this.$store.getters.PRODUCTS_v2;

            if (this.manufacturerId !== -1) {
                products = products.filter(product => product.manufacturer.id === this.manufacturerId);
            }
            if (this.hideNotInStock) {
                products = products.filter(product => product.quantity > 0);
            }
            if (this.categoryId !== -1) {
                products = products.filter(product => product.category.id === this.categoryId);
            }

            return products;
        },
    }
}
</script>

<style scoped lang="scss">
* {
}

h5 {
    color: #fff;
    font-weight: 300;
    font-size: 18px;
}

.top-button {
    width: 340px;
}

.background-tooman-grey {
    background-color: #444444;
}

.background-tooman-darkgrey {
    background-color: #333333;
}

.margin-28 {
    margin-top: 28px;
}

.fz-18 th, td {
    font-size: 18px !important;
}

.v-data-table {
    font-size: 18px !important;
}

.image-container {
    img {
        object-fit: contain;
        object-position: center;
    }

    position: relative;

    .delete-image {
        padding: 8px 10px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.3);
        color: #fff;
        position: absolute;
        right: 14px;
        top: 14px;
        font-size: 2rem;
        border: none;
        transition: .3s;

        &:hover {
            background-color: rgba(255, 255, 255, 0.6);
        }
    }
}
</style>
