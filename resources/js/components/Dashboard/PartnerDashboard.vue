<template>
    <div>
        <partners-info />
        <hr>
        <partner-remaining-products />
    </div>
</template>

<script>
import PartnersInfo from '@/views/Partners/PartnersInfo';
import PartnerRemainingProducts from '@/views/Partners/PartnerRemainingProducts';
export default {
    components: {PartnersInfo, PartnerRemainingProducts},
    data: () => ({}),
    computed: {},
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
