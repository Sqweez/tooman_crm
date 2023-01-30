import axiosClient from '@/utils/axiosClient';

const arrivalModule = {
    state: {
        arrivals: [],
        currentArrival: {},
        currentMoneyRate: 0,
        currentChildStore: -1,
        arrivalTemplates: [],
    },
    getters: {
        ARRIVALS: s => s.arrivals,
        ARRIVAL_TEMPLATES: s => s.arrivalTemplates,
        CURRENT_ARRIVAL: s => ({
            cart: s.currentArrival,
            moneyRate: s.currentMoneyRate,
            child_store: s.currentChildStore,
        }),
    },
    mutations: {
        SET_ARRIVALS(state, arrivals) {
            state.arrivals = arrivals;
        },
        UPDATE_CURRENT_ARRIVAL(state, payload) {
            state.currentArrival = payload;
        },
        UPDATE_MONEY_RATE(state, payload) {
            state.currentMoneyRate = payload;
        },
        UPDATE_CHILD_STORE(state, payload) {
            state.currentChildStore = payload;
        },
        UPDATE_ARRIVAL(state, payload) {
            state.arrivals = state.arrivals.map(a => {
                if (a.id === payload.id) {
                    a = payload;
                }
                return a;
            })
        },
        DELETE_ARRIVAL (state, id) {
            state.arrivals = state.arrivals.filter(a => a.id !== id);
        },
        SUBMIT_ARRIVAL (state, id) {
            // @TODO 2022-09-14T02:45:08 может будет другая логика
            state.arrivals = state.arrivals.filter(a => a.id !== id);
        },
        SET_ARRIVAL_TEMPLATES (state, templates) {
            state.arrivalTemplates = templates;
        },
        CREATE_ARRIVAL_TEMPLATE (state, template) {
            state.arrivalTemplates.push(template);
        }
    },
    actions: {
        async GET_ARRIVALS ({commit}, payload) {
            const { data: { data } } = await axiosClient.get(`/arrivals?is_completed?${+payload}`)
            commit('SET_ARRIVALS', data);
        },
        async getNotCompletedArrivals ({ dispatch }) {
            await dispatch('GET_ARRIVALS', 0);
        },
        async updateArrival ({ commit }, payload) {
            const { data: { data } } = await axiosClient.patch(`/arrivals/${payload.id}`, payload);
            commit('UPDATE_ARRIVAL', data);
        },
        async deleteArrival ({ commit }, id) {
            await axiosClient.delete(`/arrivals/${id}`);
            commit('DELETE_ARRIVAL', id);
        },
        async createArrival ({ commit }, payload) {
            await axiosClient.post(`/arrivals`, payload);
        },
        async submitArrival ({ commit }, payload) {
            await axiosClient.post(`/arrivals/${payload.id}/submit`, payload);
            commit('SUBMIT_ARRIVAL', payload.id);
        },
        async createArrivalTemplate ({ commit }, payload) {
            const { data } = await axiosClient.post('arrivals/template', payload);
            commit('CREATE_ARRIVAL_TEMPLATE', data);
        },
        async getArrivalTemplates ({ commit }) {
            const { data } = await axiosClient.get('arrivals/template');
            commit('SET_ARRIVAL_TEMPLATES', data);
        },
    }
};

export default arrivalModule;
