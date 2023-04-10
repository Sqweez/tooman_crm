<template>
    <div>
        <t-card-page title="Подробнее о товаре">
            <v-select
                label="Магазин"
                :items="stores"
                item-value="id"
                item-text="name"
                v-model="storeId"
            />
            <v-btn color="primary" @click="onSubmit" :disabled="!storeId">
                Получить отчет
            </v-btn>
            <div v-if="currentProduct">
                <h5>
                    <b>Товар:</b> {{ currentProduct.product_name }}
                </h5>
                <p>
                    <b>
                        Текущее количество:
                    </b>
                    {{ currentQuantity }}
                </p>
                <v-simple-table v-slot:default>
                    <thead>
                    <tr>
                        <th>
                            Документ
                        </th>
                        <th>
                            Начальный остаток
                        </th>
                        <th>
                            Приход
                        </th>
                        <th>
                            Расход
                        </th>
                        <th>
                            Конечный остаток
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, idx) of productMovements" :key="idx">
                        <td>{{ item.name }}</td>
                        <td>{{ item.start_quantity }}</td>
                        <td>{{ item.count > 0 ? item.count : '-' }}</td>
                        <td>{{ item.count < 0 ? item.count * -1 : '-' }}</td>
                        <td>{{ item.final_quantity }}</td>
                    </tr>
                    </tbody>
                </v-simple-table>
            </div>
        </t-card-page>
        <t-card-page title="Поставки товара" v-if="batches">
            <v-simple-table v-slot:default>
                <thead>
                <tr>
                    <th>
                        Текущий остаток
                    </th>
                    <th>
                        Текущая закупочная стоимость
                    </th>
                    <th>
                        Дата создания
                    </th>
                    <th>
                        Количество проданных единиц
                    </th>
                    <th>
                        Действие
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, idx) of batches" :key="item.id">
                    <td class="text-center">{{ item.quantity }} шт.</td>
                    <td class="text-center">
                        <v-text-field
                            type="number"
                            v-model="batches[idx].purchase_price"
                        />
                    </td>
                    <td>{{ item.date }}</td>
                    <td>{{ item.sale_product_count }}</td>
                    <td>
                        <v-btn color="success" small depressed @click="updatePurchasePrice(item)">
                            Обновить <v-icon>mdi-sync</v-icon>
                        </v-btn>
                    </td>
                </tr>
                </tbody>
            </v-simple-table>
        </t-card-page>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';

export default {
    data: () => ({
        storeId: null,
        currentQuantity: null,
        currentProduct: null,
        productMovements: null,
        batches: null,
    }),
    computed: {
        stores () {
            return this.$store.getters.stores;
        },
    },
    methods: {
        async onSubmit () {
            this.$loading.enable();
            const params = new URLSearchParams({
                store: this.storeId,
                product: this.$route.params.product,
            })
            const { data } = await axiosClient.get(`v2/products/movements?${params}`);
            this.currentProduct = data.product;
            this.currentQuantity = data.current;
            this.productMovements = data.output;
            this.batches = data.batches;
            this.$loading.disable();
        },
        async updatePurchasePrice (item) {
            this.$loading.enable();
            await axiosClient.post(
                `v2/products/batches/${item.id}/update`,
                { price: item.purchase_price }
            );
            await this.onSubmit();
        }
    },
    async mounted() {
        const params = new URLSearchParams(this.$route.params);

    }
}
</script>

<style scoped lang="scss">

</style>
