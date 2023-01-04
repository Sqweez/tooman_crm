export default {
    data: () => ({
        categoryId: -1,
        manufacturerId: -1,
        subcategoryId: -1,
    }),
    computed: {
        manufacturers() {
            return [
                {
                    id: -1,
                    manufacturer_name: 'Все'
                },
                ...this.$store.getters.manufacturers
            ];
        },
        categories() {
            return [{
                id: -1,
                name: 'Все'
            }, ...this.$store.getters.categories];
        },
        subcategories() {
            return [
                {
                    id: -1,
                    subcategory_name: 'Все'
                }, ...this.categories
                    .find(c => c.id === this.categoryId)
                    .subcategories || []];
        },
    },
}
