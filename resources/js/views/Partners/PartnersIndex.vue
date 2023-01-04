<template>
    <div>
        <t-card-page title="Список партнеров">
            <v-data-table
                :items="partners"
                :headers="headers"
            >
                <template v-slot:item.actions="{ item }">
                    <v-btn icon @click="$router.push(`/users/partners/${item.id}/edit`)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon @click="$router.push(`/users/partners/${item.id}/info`)">
                        <v-icon>mdi-information-outline</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </t-card-page>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';

export default {
    data: () => ({
        partners: [],
        headers: [
            {
                value: 'name',
                text: 'Имя'
            },
            {
                value: 'login',
                text: 'Логин'
            },
            {
                value: 'actions',
                text: 'Действие'
            }
        ],
    }),
    computed: {},
    methods: {},
    async mounted () {
        this.$loading.enable();
        const { data: { data } } = await axiosClient.get(`/v2/partners`);
        this.partners = data;
        this.$loading.disable();
    },
}
</script>

<style scoped lang="scss">

</style>
