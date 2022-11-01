<template>
    <div>
        <t-card-page title="Открытие смены">
            <v-text-field
                label="Сумма денег в кассе"
                type="number"
                v-model.number="cashInHand"
            />
            <v-btn color="success" @click="createWorkingDay">
                Открыть смену <v-icon>mdi-check</v-icon>
            </v-btn>
        </t-card-page>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';

export default {
    data: () => ({
        cashInHand: 0,
        hasError: false,
    }),
    beforeMount() {
        if (this.$user && !this.$user.must_open_working_day) {
            this.$toast.error('Доступ запрещен!');
            return this.$router.push('/');
        }
    },
    computed: {},
    methods: {
        async createWorkingDay () {
            if (this.cashInHand === '') {
                return this.$toast.error('Введите сумму денег в кассе');
            }
            const payload = {
                opening_cash_in_hand: this.cashInHand,
                store_id: this.$user.store_id,
            };

            if (this.hasError) {
                payload.retry = 1;
            }

            try {
                this.$loading.enable();
                await axiosClient.post(`/v2/working-day`, payload);
                this.$toast.success('Смена успешно открыта!');
                setTimeout(() => {
                    window.location = '/';
                }, 1500);
            } catch (e) {
                const message = e.response.data.message;
                this.$toast.error(message);
                this.hasError = true;
            } finally {
                this.$loading.disable();
            }
        },
    }
}
</script>

<style scoped lang="scss">

</style>
