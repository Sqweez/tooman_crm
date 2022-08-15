<template>
    <div>
        <v-card>
            <v-card-title>
                Ревизия
            </v-card-title>
            <v-card-text>
                <v-tabs v-model="tab">
                    <v-tab v-for="(tab, idx) of tabs" :key="idx">
                        {{ tab }}
                    </v-tab>
                </v-tabs>
                <v-tabs-items v-model="tab">
                    <v-tab-item>
                        <revision-create />
                    </v-tab-item>
                    <v-tab-item>
                        <revision-list />
                    </v-tab-item>
                </v-tabs-items>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import RevisionCreate from "@/views/Revision/RevisionCreate";
import RevisionList from "@/views/Revision/RevisionList";
export default {
    components: {RevisionList, RevisionCreate},
    data: () => ({
        tab: 0,
        tabs: [
            'Создать',
            'Список',
        ]
    }),
    computed: {},
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
