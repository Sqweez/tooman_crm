import Vue from 'vue'
import VueRouter from "vue-router";
import routes from "./routes";
import store from "@/store";
import ToastService from "@/utils/toastService";

const $toast = new ToastService();

Vue.use(VueRouter);

const Router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes,
    scrollBehavior(to, from, savePos) {
        return {x: 0, y: 0};
    }
});

Router.beforeEach(async (to, from, next) => {
    if (!store.getters.LOGIN_CHECKED) {
        await store.dispatch('AUTH');
    }

    const CURRENT_ROLE = `IS_${store.getters.CURRENT_ROLE.toUpperCase()}`;
    const IS_GUEST = store.getters.IS_GUEST;
    const BASE_ROUTE = IS_GUEST ? '/login' : '/';
    const GUEST_PAGES = !!(to.meta?.CAN_ENTER?.IS_GUEST);
    const IS_NON_REVISION_PAGES_BLOCKED = store.getters.USER && store.getters.USER.is_non_revision_pages_blocked;
    const MUST_OPEN_WORKING_DAY = store.getters.USER && store.getters.USER.must_open_working_day;

    const HAS_CAN_ENTER = (() => {
        return !!(
            to.meta.CAN_ENTER && Object.keys(to.meta.CAN_ENTER).length > 0
        );
    })();



    if (IS_GUEST && !GUEST_PAGES) {
        next(BASE_ROUTE);
        return;
    }

    if (HAS_CAN_ENTER) {
        const CAN_ENTER_ROLES = to.meta.CAN_ENTER;
        const ACCESS_DENIED = !!CAN_ENTER_ROLES.ACCESS_DENIED;
        if (ACCESS_DENIED) {
            return next(BASE_ROUTE);
        }
        const CAN_ENTER = !!CAN_ENTER_ROLES[CURRENT_ROLE];
        if (!CAN_ENTER) {
            next(BASE_ROUTE);
            $toast.error('Доступ запрещен!')
            return;
        }
    }

    if (IS_NON_REVISION_PAGES_BLOCKED && to.path !== '/revision') {
        return next('/revision');
    }

    if (MUST_OPEN_WORKING_DAY && to.path !== '/working-day/create') {
        return next('/working-day/create');
    }

    next();
});

export default Router;
