<template>
    <div>
        <t-card-page title="ЗП ведомость">
            Здесь будут ЗП ведомости...
        </t-card-page>
    </div>
</template>

<script>
export default {
    beforeMount() {
        if (!this.IS_SUPERUSER && !this.IS_ACCOUNTING) {
            this.$toast.error('Доступ запрещен!');
            return this.$router.push('/');
        }
    },
    data: () => ({}),
    computed: {},
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
