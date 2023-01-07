export default {
    state: {
        withdrawals: [],
        checkouts: [],
        withdrawal_types: [],
    },
    getters: {
        withdrawals: s => s.withdrawals,
        withdrawal_types: s => s.withdrawal_types,
        withdrawal_types_filter: s => [
            {
                id: -1,
                name: 'Все'
            },
            ...s.withdrawal_types
        ],
        checkouts: s => s.checkouts,
    },
    mutations: {
        SET_WITHDRAWALS (state, payload) {
            state.withdrawals = payload;
        },
        SET_WITHDRAWALS_TYPES (state, payload) {
            state.withdrawal_types = payload;
        },
        CREATE_WITHDRAWAL (state, payload) {
            state.withdrawals.push(payload);
        },
        DELETE_WITHDRAWAL (state, id) {
            state.withdrawals = state.withdrawals.filter(s => s.id !== id);
        },
        SET_CHECKOUT (state, payload) {
            state.checkouts = payload;
        },
        CREATE_CHECKOUT (state, payload) {
            state.checkouts.push(payload);
        },
        DELETE_CHECKOUT (state, id) {
            state.checkouts = state.checkouts.filter(s => s.id !== id);
        }
    },
}
