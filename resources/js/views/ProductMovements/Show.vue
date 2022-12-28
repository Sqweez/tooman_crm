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
            <v-btn color="primary" @click="onSubmit">
                Получить отчет
            </v-btn>
        </t-card-page>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';

export default {
    data: () => ({
        storeId: null,
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
            console.log(data);
            this.$loading.disable();
        },
    },
    async mounted() {
        const params = new URLSearchParams(this.$route.params);

    }
}
</script>

<style scoped lang="scss">

</style>
