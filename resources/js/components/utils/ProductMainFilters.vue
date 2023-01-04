<template>
    <v-row>
        <v-col cols="12" xl="4">
            <v-autocomplete
                label="Производитель"
                :items="manufacturers"
                v-model="manufacturerId"
                item-value="id"
                item-text="manufacturer_name"
            />
        </v-col>
        <v-col cols="12" xl="4">
            <v-autocomplete
                label="Категория"
                :items="categories"
                v-model="categoryId"
                item-value="id"
                item-text="name"
            />
        </v-col>
        <v-col cols="12" xl="4">
            <v-autocomplete
                label="Подкатегория"
                :items="subcategories"
                v-model="subcategoryId"
                item-value="id"
                item-text="subcategory_name"
            />
        </v-col>
    </v-row>
</template>

<script>
export default {
    data: () => ({}),
    methods: {}
}
</script>

<style scoped lang="scss">

</style>
