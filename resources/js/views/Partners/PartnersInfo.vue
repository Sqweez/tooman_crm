<template>
    <div>
        <t-card-page title="Информация по продажам партнерских товаров">
            <i-date-picker
                label="Период"
                v-model="dates"
            />
            <v-btn color="success" @click="onSubmit">
                Получить отчет <v-icon>mdi-information-outline</v-icon>
            </v-btn>
            <div v-if="products">
                <v-list>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ totalAmount | priceFilters }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                Общая сумма
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
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
                </v-row>
                <v-data-table
                    :headers="headers"
                    :items="filteredProducts"
                >
                    <template v-slot:item.total_purchase_price="{ item }">
                        {{ item.total_purchase_price | priceFilters }}
                    </template>
                </v-data-table>
            </div>
        </t-card-page>
    </div>
</template>

<script>
import IDatePicker from '@/components/DatePicker/DatePicker';
import axiosClient from '@/utils/axiosClient';
import productFiltersMixin from '@/mixins/productFiltersMixin';
export default {
    components: {IDatePicker},
    mixins: [productFiltersMixin],
    data: () => ({
        dates: [],
        headers: [
            {
                text: 'Товар',
                value: 'product.product_name',
            },
            {
                text: 'Количество',
                value: 'count'
            },
            {
                text: 'Общая закупочная стоимость',
                value: 'total_purchase_price'
            }
        ],
        products: undefined,
    }),
    computed: {
        totalAmount () {
            if (!this.products) {
                return 0;
            }
            return this.products.reduce((a, c) => {
                return a + c.total_purchase_price;
            }, 0);
        },
        filteredProducts () {
            if (!this.products) {
                return [];
            }
            let products = this.products;
            if (this.manufacturerId !== -1) {
                products = products.filter(p => p.product.manufacturer.id === this.manufacturerId);
            }

            if (this.categoryId !== -1) {
                products = products.filter(p => p.product.category.id === this.categoryId);
            }

            if (this.subcategoryId !== -1) {
                products = products.filter(p => p.product.subcategory_id === this.subcategoryId);
            }
            return products;
        }
    },
    methods: {
        async onSubmit () {
            try {
                this.$loading.enable();
                this.products = undefined;
                const partnerId = this.$route.params?.id ? this.$route.params.id : this.$user.id;
                const payload = new URLSearchParams({
                    start: this.dates[0],
                    finish: this.dates[1]
                })
                const { data } = await axiosClient.get(`/v2/partners/${partnerId}?${payload}`)
                this.products = data;
            } catch (e) {
                console.log(e);
                this.$toast.error('Произошла ошибка!')
            } finally {
                this.$loading.disable();
            }
        },
    }
}
</script>

<style scoped lang="scss">

</style>
