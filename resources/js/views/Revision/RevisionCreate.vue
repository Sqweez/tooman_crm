<template>
    <div class="my-4">
        <v-select
            label="Ответственный"
            :items="$users"
            item-value="id"
            item-text="name"
            v-model="userId"
            :disabled="!IS_SUPERUSER"
        />
        <v-select
            label="Склад"
            :items="$stores"
            item-value="id"
            item-text="name"
            v-model="storeId"
            :disabled="!IS_SUPERUSER"
        />
        <v-btn color="success" @click="onSubmit">
            Создать
        </v-btn>
    </div>
</template>

<script>
import axios from "axios";
export default {
    data: () => ({
        userId: null,
        storeId: null,
    }),
    computed: {},
    mounted() {
        this.init();
    },
    methods: {
        init () {
            this.userId = this.$user.id;
            this.storeId = this.$user.store_id;
        },
        async onSubmit () {
            try {
                this.$loading.enable('Пожалуйста, подождите...');
                const payload = {
                    user_id: this.userId,
                    store_id: this.storeId,
                };
                const { data } = await axios.post('/api/v2/revision', payload);
                this.$file.download(data.path);
                this.$store.commit('addRevision', data.revision);
                this.$toast.success('Ревизия успешно создана!');
            } catch (e) {
                this.$toast.error('Произошла ошибка, обратитесь в службу поддержки!')
            } finally {
                this.$loading.disable();
            }
        }
    },
}
</script>

<style scoped lang="scss">

</style>
