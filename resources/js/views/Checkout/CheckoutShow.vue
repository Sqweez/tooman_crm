<template>
    <div>
        <v-row>
            <v-col cols="12" xl="4">
                <IDatePicker />
            </v-col>
            <v-col cols="12" xl="4">
                <v-select
                    label="Склад"
                    v-model="storeId"
                    :items="$storeFilters"
                    item-value="id"
                    item-text="name"
                />
                <v-select
                    label="Пользователь"
                    v-model="userId"
                    :items="$userFilters"
                    item-value="id"
                    item-text="name"
                />
            </v-col>
            <v-col cols="12" xl="4">
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
            </v-col>
        </v-row>
        <v-data-table
            loading-text="Идет загрузка товаров..."
            class="background-iron-grey fz-18"
            no-results-text="Нет результатов"
            no-data-text="Нет данных"
            :items="filteredItems"
            :headers="headers"
            :footer-props="{
                            'items-per-page-options': [10, 15, {text: 'Все', value: -1}],
                            'items-per-page-text': 'Записей на странице',
                        }"
        >
            <template v-slot:item.amount="{ item }">
                {{ item.amount | priceFilters }}
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn color="error" :disabled="!item.can_delete" @click="id = item.id; showModal = true;">
                    Удалить <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
        </v-data-table>
        <ConfirmationModal
            :state="showModal"
            :on-confirm="onDelete"
            v-on:cancel="id = null; showModal = false"
            message="Вы действительно хотите удалить выбранное изъятие?"
        />
    </div>
</template>

<script>
import axios from "axios";
import moment from 'moment';
import ConfirmationModal from "@/components/Modal/ConfirmationModal";
import IDatePicker from '@/components/DatePicker/DatePicker';

export default {
    components: {IDatePicker, ConfirmationModal},
    name: 'CheckoutShow',
    data: () => ({
        id: null,
        showModal: false,
        userId: -1,
        storeId: -1,
        typeId: -1,
        headers: [
            {
                value: 'amount',
                text: 'Сумма'
            },
            {
                value: 'user.name',
                text: 'Пользователь'
            },
            {
                value: 'store.name',
                text: 'Склад'
            },
            {
                value: 'description',
                text: 'Комментарий'
            },
            {
                value: 'date',
                text: 'Дата'
            },
            {
                value: 'actions',
                text: 'Действие'
            }
        ],
        dates: [],
    }),
    computed: {
        items () {
            return this.$store.getters.checkouts;
        },
        filteredItems () {
            return this.items.filter(i => {
                return this.storeId === -1 ? true : i.store.id === this.storeId;
            }).filter(i => {
                return this.userId === - 1 ? true : i.user.id === this.userId;
            }).filter(i => {
                return this.typeId === - 1 ? true : i.type_id === this.typeId;
            }).filter(i => {
                if (!(this.start && this.finish)) {
                    return true;
                }
                const startDate = moment(this.start);
                const endDate = moment(this.finish);
                const currentDate = moment(i.created_at);
                return currentDate.isSameOrAfter(startDate, 'day') && currentDate.isSameOrBefore(endDate, 'day');
            });
        },
        totalAmount () {
            return this.filteredItems.reduce((a, c) => a + c.amount, 0);
        }
    },
    methods: {
        async getItems () {
            try {
                this.$loading.enable();
                const { data: { data } } = await axios.get('/api/v2/checkout');
                this.$store.commit('SET_CHECKOUT', data);
            } catch (e) {
            } finally {
                this.$loading.disable();
            }
        },
        async onDelete () {
            await axios.delete(`/api/v2/checkout/${this.id}`);
            this.$store.commit('DELETE_CHECKOUT', this.id);
            this.id = null;
            this.showModal = false;
            this.$toast.success('Внесение удалено!');
        }
    },
    async mounted() {
        await this.getItems();
    }
}
</script>

<style scoped lang="scss">

</style>
