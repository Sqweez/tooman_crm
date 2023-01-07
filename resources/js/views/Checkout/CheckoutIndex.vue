<template>
    <div>
        <v-card>
            <v-card-title>Внесения в кассу</v-card-title>
            <v-card-text>
                <v-tabs v-model="tab">
                    <v-tab v-for="(tab, idx) of tabs" :key="idx">
                        {{ tab.value }}
                    </v-tab>
                </v-tabs>
                <v-tabs-items v-model="tab">
                    <v-tab-item>
                        <checkout-create />
                    </v-tab-item>
                    <v-tab-item>
                        <checkout-show />
                    </v-tab-item>
                </v-tabs-items>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';
import CheckoutCreate from '@/views/Checkout/CheckoutCreate';
import CheckoutShow from '@/views/Checkout/CheckoutShow';
export default {
    components: {CheckoutShow, CheckoutCreate},
    data: () => ({
        tab: 0,
        tabs: [
            {
                value: 'Создание',
                component: 'CheckoutCreate'
            },
            {
                value: 'Список',
                component: 'CheckoutShow'
            }
        ]
    }),
    computed: {
        component () {
            return this.tabs[this.tab].component;
        }
    },
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
