<template>
    <div>
        <t-card-page title="Закрытие смены">
            <v-text-field
                label="Денег в кассе"
                v-model.number="cashInHand"
                type="number"
            />
            <v-text-field
                label="Сумма продаж каспи-перевод"
                v-model.number="kaspiTransferCash"
                type="number"
            />
            <v-text-field
                label="Сумма продаж наличные"
                v-model.number="hardCash"
                type="number"
            />
            <v-text-field
                label="Сумма продаж каспи-терминал"
                v-model.number="kaspiTerminalCash"
                type="number"
            />
            <v-text-field
                label="Сумма продаж jysan"
                v-model.number="jysanCash"
                type="number"
            />
            <v-btn color="error" @click="closeWorkingDay">
                Закрыть смену <v-icon>mdi-check</v-icon>
            </v-btn>
        </t-card-page>
    </div>
</template>

<script>
import axiosClient from '@/utils/axiosClient';

export default {
    beforeMount() {
        if (this.$user && !this.$user.working_day_id) {
            this.$toast.error('Доступ запрещен!');
            return this.$router.push('/');
        }
    },
    data: () => ({
        cashInHand: 0,
        jysanCash: 0,
        kaspiTransferCash: 0,
        kaspiTerminalCash: 0,
        hardCash: 0,
    }),
    computed: {},
    methods: {
        async closeWorkingDay () {
            if ([this.cashInHand, this.jysanCash, this.kaspiTransferCash, this.kaspiTerminalCash, this.hardCash].includes('')) {
                return this.$toast.error('Заполните все данные!');
            }
            const payload = {
                closing_cash_in_hand: this.cashInHand,
                kaspi_transfers_cash: this.kaspiTransferCash,
                kaspi_terminal_cash: this.kaspiTerminalCash,
                jysan_transfers_cash: this.jysanCash,
                hard_cash: this.hardCash,
                working_day_id: this.$user.working_day_id,
            };

            try {
                this.$loading.enable();
                await axiosClient.patch('/v2/working-day/close', payload);
                this.$toast.success('Смена успешно закрыта!');
                await this.$store.dispatch('LOGOUT');
                window.location.reload();
            } catch (e) {
                this.$toast.error(e.response.data.message);
            } finally {
                this.$loading.disable();
            }
        }
    }
}
</script>

<style scoped lang="scss">

</style>
