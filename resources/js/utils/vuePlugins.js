import ToastService from "@/utils/toastService";
import LoadingService from "@/utils/loadingService";
import ColorService from "@/utils/colorService";
import store from "@/store";
import FileService from "@/utils/fileService";
import EconomyService from "@/utils/economyService";
import DatePlugin from "@/utils/datePlugin";
import {mapGetters} from 'vuex';

export default {
    install(Vue, options) {
        Vue.mixin({
            methods: {
                $evaluate: param => eval('this.'+param)
            },
            computed: {
                ...mapGetters({
                    $stores: 'stores',
                    $storeFilters: 'store_filters',
                    $users: 'users',
                    $userFilters: 'user_filters'
                }),
                $toast() {
                    return new ToastService();
                },
                $date() {
                    return new DatePlugin();
                },
                $loading() {
                    return new LoadingService(store)
                },
                $color() {
                    return new ColorService();
                },
                $file() {
                    return new FileService();
                },
                $economy () {
                    return new EconomyService();
                },
                $user () {
                    return this.$store.getters.USER;
                },
                isSeller() {
                    return this.$store.getters.IS_SELLER;
                },
                isAdmin() {
                    return this.$store.getters.IS_ADMIN;
                },
                IS_OBSERVER() {
                    return this.$store.getters.IS_OBSERVER;
                },
                IS_BOSS() {
                    return this.$store.getters.IS_BOSS;
                },
                IS_SUPERUSER() {
                    return this.IS_BOSS || this.isAdmin || this.IS_MARKETOLOG || this.IS_MANAGER || this.IS_GENERAL_MANAGER || this.IS_ACCOUNTING;
                },
                IS_MARKETOLOG () {
                    return this.$store.getters.IS_MARKETOLOG;
                },
                IS_SENIOR_SELLER() {
                    return this.$store.getters.IS_SENIOR_SELLER;
                },
                IS_LOADING_STATE() {
                    return this.$store.getters.isLoading;
                },
                IS_LOADING() {
                    return this.$store.getters.IS_LOADING;
                },
                IS_MODERATOR () {
                    return this.$store.getters.IS_MODERATOR;
                },
                IS_FRANCHISE () {
                    return this.$store.getters.IS_FRANCHISE;
                },
                IS_MANAGER () {
                    return this.$store.getters.IS_MANAGER;
                },
                IS_GENERAL_MANAGER () {
                    return this.$store.getters.IS_GENERAL_MANAGER;
                },
                IS_ACCOUNTING () {
                    return this.$store.getters.IS_ACCOUNTING;
                },
            }
        })
    }
}
