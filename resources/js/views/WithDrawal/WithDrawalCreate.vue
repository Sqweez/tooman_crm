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
                        label="Тип изъятия"
                        v-model="type_id"
                        :items="types"
                        item-text="name"
                        item-value="id"
                    />
                    <v-select
                        label="Счет изъятия"
                        v-model="account"
                        :items="accounts"
                        item-text="name"
                        item-value="id"
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
                    <input type="file" ref="fileInput" class="d-none" @change="onFileInputChange" accept="image/*">
                    <div class="my-4" v-if="imagePreview" style="width: 300px; height: 300px; overflow: hidden;">
                        <img :src="imagePreview" style="object-fit: contain; object-position: center; width: 100%; height: 100%;">
                    </div>
                    <v-btn color="primary" type="button" @click="$refs.fileInput.click()">
                        Загрузить изображение
                    </v-btn>
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
import accounts from '@/common/enums/accounts';

export default {
    data: () => ({
        imagePreview: null,
        amount: 0,
        description: '',
        image: null,
        store_id: null,
        user_id: null,
        type_id: 0,
        account: 0,
        accounts: accounts,
    }),
    mounted() {
        this.init();
    },
    computed: {
        types () {
            return this.$store.getters.withdrawal_types;
        }
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
            if (!this.image) {
                return this.$toast.error('Прикрепите изображение!');
            }
            const payload = {
                image: this.image,
                amount: this.amount,
                description: this.description,
                user_id: this.user_id,
                store_id: this.store_id,
                type_id: this.type_id,
                account: this.account,
            };

            const formData = new FormData;
            Object.keys(payload).forEach(key => {
                formData.append(key, payload[key]);
            })

            const { data: { data } } = await axios.post('/api/v2/with-drawal', formData);
            this.$store.commit('CREATE_WITHDRAWAL', data);
            this.init();
            this.$toast.success('Изъятие добавлено!');
        }
    }
}
</script>

<style scoped lang="scss">

</style>
