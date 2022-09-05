<template>
    <v-dialog
        v-model="dialog"
        :max-width="options.width"
        :style="{ zIndex: options.zIndex }"
        @keydown.esc="cancel"
    >
        <v-card>
            <v-card-title class="headline d-flex justify-space-between">
                <span class="white--text">{{ title }}</span>
                <v-btn icon text class="float-right">
                    <v-icon color="white" @click="cancel">
                        mdi-close
                    </v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="modal-text">
                {{ message }}
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-btn text @click="cancel">
                    Отмена
                </v-btn>
                <v-spacer/>
                <v-btn color="success" text @click="agree">
                    Подтвердить
                    <v-icon>mdi-check</v-icon>
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "ConfirmationDialog",
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            message: null,
            title: null,
            options: {
                color: "grey lighten-3",
                width: 500,
                zIndex: 200,
                noconfirm: false,
            },
        };
    },

    methods: {
        open(message = 'Вы уверены, что хотите выполнить выбранное действие?', title = 'Подтвердите действие', options) {
            this.dialog = true;
            this.title = title;
            this.message = message;
            this.options = Object.assign(this.options, options);
            return new Promise((resolve, reject) => {
                this.resolve = resolve;
                this.reject = reject;
            });
        },
        agree() {
            this.resolve(true);
            this.dialog = false;
        },
        cancel() {
            this.resolve(false);
            this.dialog = false;
        },
    },
};
</script>
