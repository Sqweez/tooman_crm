<template>
    <div>
        <v-card>
            <v-card-title>
                Оприходования
            </v-card-title>
            <v-card-text>
                <v-tabs v-model="tab">
                    <v-tab v-for="(tab, idx) of tabs" :key="idx">
                        {{ tab }}
                    </v-tab>
                </v-tabs>
                <v-tabs-items v-model="tab">
                    <v-tab-item>
                        <posting-list />
                    </v-tab-item>
                    <v-tab-item>
                        <posting-create />
                    </v-tab-item>
                </v-tabs-items>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import PostingList from '@/views/Posting/PostingList';
import PostingCreate from '@/views/Posting/PostingCreate';
export default {
    components: {PostingCreate, PostingList},
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
