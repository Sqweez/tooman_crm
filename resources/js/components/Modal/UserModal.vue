<template>
    <v-dialog max-width="900" v-model="state" persistent>
        <v-card>
            <v-card-title class="headline justify-space-between">
                <span class="white--text">{{ id !== null ? 'Редактирование' : 'Добавление' }} пользователя</span>
                <v-btn icon text class="float-right" @click="$emit('cancel')">
                    <v-icon color="white">
                        mdi-close
                    </v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                <v-form class="p-2">
                    <v-text-field label="Имя" v-model.trim="user.name" autofocus/>
                    <v-text-field label="Логин" v-model.trim="user.login"/>
                    <v-text-field
                        :label="id === null ? 'Пароль' : 'Новый пароль'"
                        type="password"
                        v-model="user.password"
                        v-if="changePass || id === null"
                    />
                    <v-checkbox label="Сменить пароль?" v-model="changePass" v-if="id !== null"/>
                    <v-select
                        class="mt-3"
                        label="Роль"
                        item-value="id"
                        item-text="role_name"
                        :items="roles"
                        v-model="user.role_id"
                    />
                    <div class="d-flex align-center" v-if="!isGeneralManager">
                        <v-select
                            class="mt-3"
                            label="Магазин"
                            :items="stores"
                            item-text="name"
                            item-value="id"
                            v-model="user.store_id"
                        />
                    </div>
                    <div class="d-flex align-center" v-else>
                        <v-select
                            class="mt-3"
                            label="Магазины"
                            :items="stores"
                            item-text="name"
                            item-value="id"
                            v-model="user_stores"
                            multiple
                        />
                    </div>
                </v-form>
            </v-card-text>
            <v-card-actions class="p-2" v-if="!loading">
                <v-btn text @click="$emit('cancel')">Отмена</v-btn>
                <v-spacer></v-spacer>
                <v-btn
                    text
                    type="submit"
                    color="success"
                    @click="submit"
                >
                    Сохранить
                    <v-icon>mdi-check</v-icon>
                </v-btn>
            </v-card-actions>
            <v-progress-linear
                indeterminate
                :active="loading"
                color="green"
                absolute
                bottom
            ></v-progress-linear>
        </v-card>
    </v-dialog>
</template>

<script>
    import ACTIONS from "@/store/actions";

    export default {
        watch: {
            state() {
                this.user = {};
                this.user_stores = [];
                if (this.id !== null) {
                    this.user = {...this.$store.getters.user(this.id)};
                    this.user_stores = this.user.stores.map(s => s.id);
                }
            }
        },
        data: () => ({
            loading: false,
            user: {},
            changePass: false,
            user_stores: [],
        }),
        props: {
            state: {
                type: Boolean,
                default: true,
            },
            id: {
                type: Number,
                default: null,
            }
        },
        computed: {
            roles() {
                return this.IS_BOSS ? this.$store.getters.user_roles : this.$store.getters.user_roles.filter(r => r.id !== 8);
            },
            stores() {
                return this.$store.getters.stores;
            },
            shops() {
                return this.$store.getters.shops;
            },
            isGeneralManager () {
                return this.user.role_id === 14;
            }
        },
        methods: {
            async createUser() {
                const user = {
                    name: this.user.name,
                    login: this.user.login,
                    password: this.user.password,
                    role_id: this.user.role_id,
                    store_id: this.user.store_id,
                };

                if (this.isGeneralManager) {
                    user.store_id = this.user_stores[0];
                }

                if (!Object.keys(user).every(key => !!user[key])) {
                    return this.$toast.error('Заполните все поля!');
                }

                user.stores = this.user_stores;

                await this.$store.dispatch(ACTIONS.CREATE_USER, user);
                this.$toast.success('пользователь создан');
                this.$emit('onSubmit');
            },
            async editUser() {
                const user = {
                    id: this.id,
                    name: this.user.name,
                    login: this.user.login,
                    role_id: this.user.role_id,
                    store_id: this.user.store_id
                };

                if (this.changePass) {
                    user.password = this.user.password;
                }

                if (this.isGeneralManager) {
                    user.store_id = this.user_stores[0];
                }
                user.stores = this.user_stores;

                if (!Object.keys(user).every(key => !!user[key])) {
                    return this.$toast.error('Заполните все поля!');
                }

                await this.$store.dispatch(ACTIONS.EDIT_USER, user);

                this.$toast.success('пользователь отредактирован');
                this.$emit('onSubmit')

            },
            async submit() {
                if (this.id !== null) {
                    await this.editUser();
                } else {
                    await this.createUser();
                }
            }
        }
    }
</script>

