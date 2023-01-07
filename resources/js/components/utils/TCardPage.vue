<template>
    <div>
        <v-card style="overflow-x: scroll;">
            <v-card-title>
                {{ title }}
            </v-card-title>
            <v-card-text>
                <slot />
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
export default {
    name: 'TCardPage',
    data: () => ({}),
    computed: {},
    methods: {},
    props: {
        title: {
            type: String,
            default: 'Страница'
        }
    }
}
</script>

<style scoped lang="scss">

</style>
