<template>
    <div>
        <v-card>
            <v-card-title>
                Списания
            </v-card-title>
            <v-card-text>
                <v-tabs v-model="tab">
                    <v-tab v-for="(tab, idx) of tabs" :key="idx">
                        {{ tab }}
                    </v-tab>
                </v-tabs>
                <v-tabs-items v-model="tab">
                    <v-tab-item>
                        <write-off-list />
                    </v-tab-item>
                    <v-tab-item>
                        <write-off-create />
                    </v-tab-item>
                </v-tabs-items>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import WriteOffList from "@/views/WriteOffs/WriteOffList";
import WriteOffCreate from "@/views/WriteOffs/WriteOffCreate";
export default {
    components: {WriteOffList, WriteOffCreate},
    data: () => ({
        tab: 0,
        tabs: [
            'Список',
            'Создать',
        ]
    }),
    computed: {},
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
