<template>
    <div>
        <v-card>
            <v-card-text>
                <v-form @submit.prevent="onSubmit">
                    <v-text-field
                        label="Сумма"
                        v-model.number="amount"
                        type="number"
                    />
                    <v-textarea
                        label="Комментарий"
                        v-model.trim="description"
                    />
                    <v-select
                        v-if="IS_SUPERUSER"
                        label="Склад"
                        v-model="store_id"
                        :items="$stores"
                        item-text="name"
                        item-value="id"
                    />
                    <v-select
                        v-if="IS_SUPERUSER"
                        label="Пользователь"
                        v-model="user_id"
                        :items="$users"
                        item-text="name"
                        item-value="id"
                    />
                    <div class="mt-4">
                        <v-btn color="success" type="submit">
                            Сохранить <v-icon>mdi-check</v-icon>
                        </v-btn>
                    </div>
                </v-form>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data: () => ({
        imagePreview: null,
        amount: 0,
        description: '',
        image: null,
        store_id: null,
        user_id: null,
        type_id: 0,
    }),
    name: 'CheckoutCreate',
    mounted() {
        this.init();
    },
    methods: {
        init () {
            this.store_id = this.$user.store_id;
            this.user_id = this.$user.id;
            this.image = null;
            this.imagePreview = null;
            this.amount = 0;
            this.description = '';
        },
        onFileInputChange (e) {
            this.image = e.target.files[0];
            this.imagePreview = URL.createObjectURL(this.image);
            this.$refs.fileInput.value = null;
        },
        async onSubmit () {
            const payload = {
                amount: this.amount,
                description: this.description,
                user_id: this.user_id,
                store_id: this.store_id,
            };

            const formData = new FormData;
            Object.keys(payload).forEach(key => {
                formData.append(key, payload[key]);
            })

            const { data: { data } } = await axios.post('/api/v2/checkout', formData);
            this.$store.commit('CREATE_CHECKOUT', data);
            this.init();
            this.$toast.success('Внесение добавлено!');
        }
    }
}
</script>

<style scoped lang="scss">

</style>
