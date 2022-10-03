<template>
    <base-modal :state="state" title="Информация об оприходовании" @cancel="$emit('cancel')">
        <template #default>
            <v-simple-table v-slot:default class="mt-5">
                <template>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Наименование</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Стоимость</th>
                        <th v-if="false">Удалить</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) of $store.getters.POSTING.products" :key="item.id">
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
                        <td>
                            {{ item.quantity }}
<!--                            <v-btn icon color="error" @click="decreaseCartCount(index)">
                                <v-icon>mdi-minus</v-icon>
                            </v-btn>-->
<!--                            <p
                                style="min-width: 40px; max-width: 40px; text-align: center; margin-bottom: 0;"
                            >
                                {{ item.quantity }}
                            </p>-->
<!--                            <v-btn icon color="success" @click="increaseCartCount(index)">
                                <v-icon>mdi-plus</v-icon>
                            </v-btn>-->
                        </td>
                        <td>
<!--                            <v-text-field
                                v-model.number="item.product_price"
                            />-->
                            {{ item.purchase_price | priceFilters }}
                        </td>
                        <td>{{ (item.purchase_price * item.quantity) | priceFilters }}</td>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>
        </template>
        <template #actions>
            <v-spacer />
            <v-btn text @click="$emit('cancel')">
                Закрыть
            </v-btn>
        </template>
    </base-modal>
</template>

<script>
export default {
    data: () => ({}),
    computed: {},
    methods: {
        decreaseCartCount (index) {},
        increaseCartCount (index) {},
        updateCount (event, item) {},
    },
    props: {
        state: Boolean,
    }
}
</script>

<style scoped lang="scss">

</style>
