<template>
    <v-card>
        <v-card-title>
            Приемка
        </v-card-title>
        <v-card-text>
            <div
                class="text-center d-flex align-center justify-center"
                style="min-height: 651px"
                v-if="loading">
                <v-progress-circular
                    indeterminate
                    size="65"
                    color="primary"
                ></v-progress-circular>
            </div>
            <div v-if="!loading">
                <v-btn
                    v-for="(segment, index) of segments"
                    :key="index"
                    :text="currentSegment !== segment.component"
                    style="width: 200px"
                    class="mr-3"
                    @click="chooseSegment(segment)"
                    color="primary">
                    {{ segment.name }}
                </v-btn>
                <component
                    class="mt-5"
                    :is="currentSegment"/>
            </div>
        </v-card-text>
    </v-card>
</template>

<script>
    import ArrivalHistory from "@/components/Segments/Arrivals/ArrivalHistory";
    import CurrentArrivals from "@/components/Segments/Arrivals/CurrentArrivals";
    import NewArrival from "@/components/Segments/Arrivals/NewArrival";

    export default {
        components: {
            ArrivalHistory, CurrentArrivals, NewArrival
        },
        data: () => ({
            loading: false,
            currentSegment: 'CurrentArrivals'
        }),
        methods: {
            chooseSegment(segment) {
                this.currentSegment = segment.component;
            }
        },
        computed: {
            segments() {
                return (this.IS_SUPERUSER && !this.IS_MARKETOLOG) ? [
                    {
                        name: 'Текущие',
                        component: 'CurrentArrivals'
                    },
                    {
                        name: 'Создать',
                        component: 'NewArrival'
                    },
                    {
                        name: 'История',
                        component: 'ArrivalHistory'
                    },
                ] : [
                    {
                        name: 'Текущие',
                        component: 'CurrentArrivals'
                    },
                    {
                        name: 'История',
                        component: 'ArrivalHistory'
                    },
                ]
            }
        }
    }
</script>

<style scoped>

</style>
