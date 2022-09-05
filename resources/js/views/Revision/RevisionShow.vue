<template>
    <div>
        <v-card>
            <v-card-title>
                Результаты ревизии
            </v-card-title>
            <v-card-text v-if="!isLoading">
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th>Ответственный</th>
                            <th>Склад</th>
                            <th>История</th>
                            <th>Статус</th>
                            <th>Проверяющий</th>
                            <th>Файлы</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ revision.user.name }}</td>
                            <td>{{ revision.store.name }}</td>
                            <td>
                                <v-list>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>
                                                {{ revision.date }}
                                            </v-list-item-title>
                                            <v-list-item-subtitle>
                                                Дата создания
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>
                                                {{ revision.sent_to_approve_at }}
                                            </v-list-item-title>
                                            <v-list-item-subtitle>
                                                Дата отправки на проверку
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>
                                                {{ revision.edited_pivot_at }}
                                            </v-list-item-title>
                                            <v-list-item-subtitle>
                                                Дата редактирования сводной таблица
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>
                                                {{ revision.finished_at }}
                                            </v-list-item-title>
                                            <v-list-item-subtitle>
                                                Дата завершения
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </td>
                            <td>
                                {{ revision.status_text }}
                            </td>
                            <td>
                                {{  revision.checking_user.name}}
                            </td>
                            <td>
                                <v-list>
                                    <v-list-item v-for="file of revision.files" :key="file.name">
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
                            </td>
                            <td>
                                <v-btn color="primary" v-if="revision.is_finished" style="width: 290px;">
                                    Сформировать списание
                                </v-btn>
                                <v-btn color="primary" v-if="revision.is_finished" class="mt-4" style="width: 290px;">
                                    Сформировать оприходование
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-autocomplete
                            :items="manufacturers"
                            item-text="name"
                            item-value="id"
                            label="Производитель"
                            v-model="manufacturerId"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-autocomplete
                            :items="categories"
                            item-text="name"
                            item-value="id"
                            label="Категория"
                            v-model="categoryId"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-list>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ totalProductPriceSumDelta | priceFilters}}
                                    </v-list-item-title>
                                    <v-list-item-subtitle>
                                        Общее отклонение суммы
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                    </v-col>
                </v-row>
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
                    :items="products"
                    :search="search"
                    :headers="headers"
                >
                    <template v-slot:item.product_price="{ item }">
                        {{ item.product_price | priceFilters }}
                    </template>
                    <template v-slot:item.purchase_price="{ item }">
                        {{ item.purchase_price | priceFilters }}
                    </template>
                    <template v-slot:item.delta="{ item }">
                        <span v-if="item.delta < 0" style="color: red;">
                            {{ item.delta }}
                        </span>
                        <span v-else>
                            {{ item.delta }}
                        </span>
                    </template>
                    <template v-slot:item.stock_product_price_sum="{ item }">
                        {{ item.stock_product_price_sum | priceFilters }}
                    </template>
                    <template v-slot:item.fact_product_price_sum="{ item }">
                        {{ item.fact_product_price_sum | priceFilters }}
                    </template>
                    <template v-slot:item.product_price_sum_delta="{ item }">
                         <span v-if="item.product_price_sum_delta < 0" style="color: red;">
                           {{ item.product_price_sum_delta | priceFilters }}
                        </span>
                        <span v-else>
                           {{ item.product_price_sum_delta | priceFilters }}
                        </span>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex';
import Actions from '@/store/actions';

export default {
    data: () => ({
        search: '',
        headers: [
            {
                value: 'product_id',
                text: 'Артикул'
            },
            {
                value: 'full_product_name',
                text: 'Номенклатура'
            },
            {
                value: 'category',
                text: 'Категория'
            },
            {
                value: 'product_price',
                text: 'Цена'
            },
            {
                value: 'purchase_price',
                text: 'Закупочная цена'
            },
            {
                value: 'stock_quantity',
                text: 'Кол-во учет'
            },
            {
                value: 'fact_quantity',
                text: 'Кол-во факт'
            },
            {
                value: 'delta',
                text: 'Отклонение'
            },
            {
                value: 'stock_product_price_sum',
                text: 'Сумма учет'
            },
            {
                value: 'fact_product_price_sum',
                text: 'Сумма факт'
            },
            {
                value: 'product_price_sum_delta',
                text: 'Сумма отклонение'
            }
        ],
        isLoading: false,
        categoryId: -1,
        manufacturerId: -1,
    }),
    computed: {
        id () {
            return +this.$route.params.id;
        },
        revision () {
            return this.$store.getters.revision;
        },
        products () {
            return this.$store.getters.revision_products
                .filter(p => {
                    return this.categoryId === -1 ? true : p.category_id === this.categoryId;
                }).filter(p => {
                    return this.manufacturerId === -1 ? true : p.manufacturer_id === this.manufacturerId;
                });
        },
        totalProductPriceSumDelta () {
            return this.products.reduce((a, c) => {
                return a + c.product_price_sum_delta;
            }, 0);
        },
        ...mapGetters({
            categories: 'CATEGORY_FILTERS',
            manufacturers: 'MANUFACTURER_FILTERS',
        })
    },
    methods: {
        async getRevision () {
            try {
                this.$loading.enable();
                const { data } = await axios.get(`/api/v2/revision/${this.id}`);
                await this.$store.dispatch(Actions.GET_CATEGORIES);
                await this.$store.dispatch(Actions.GET_MANUFACTURERS);
                this.$store.commit('setRevision', data);
                this.isLoading = false;
            } catch (e) {
                this.$toast.error('Произошла ошибка');
                this.$router.push('/revision');
            } finally {
                this.$loading.disable();
            }

        }
    },
    async mounted() {
        await this.getRevision();
    }
}
</script>

<style scoped lang="scss">

</style>
