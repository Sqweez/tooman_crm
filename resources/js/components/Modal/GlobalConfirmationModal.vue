<template>
    <base-modal :state="state" :title="title" @cancel="_reject" width="500" persistent>
        <template #default>
            <div class="modal-text">
                {{ text }}
            </div>
        </template>
        <template #actions>
            <v-btn text @click="_reject">
                Закрыть
            </v-btn>
            <v-spacer />
            <v-btn text color="success" @click="_confirm">
                Подтвердить <v-icon>mdi-check</v-icon>
            </v-btn>
        </template>
    </base-modal>
</template>

<script>
export default {
    data: () => ({
        resolvePromise: undefined,
        rejectPromise: undefined,
    }),
    props: {
        title: {
            type: String,
            default: 'Подтвердите действие',
        },
        state: {
            type: Boolean
        },
        text: {
            type: String,
            default: 'Подтвердите выбранное действие'
        }
    },
    watch: {
        state () {
            this.show();
        }
    },
    computed: {},
    methods: {
        show () {
            return new Promise((resolve, reject) => {
                this.resolvePromise = resolve
                this.rejectPromise = reject
            })
        },
        _confirm () {
            this.$emit('confirmed', true);
        },
        _reject () {
            this.$emit('confirmed', false);
        }
    }
}
</script>

<style scoped lang="scss">
.modal-text {
    margin-top: 15px!important;;
    font-size: 16px;
}
</style>
