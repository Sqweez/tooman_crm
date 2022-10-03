import axios from 'axios';

export default {
    state: {
        postings: [],
        posting: null
    },
    getters: {
        POSTINGS: state => state.postings,
        POSTING: state => state.posting
    },
    mutations: {
        createPosting (state, payload) {
            state.postings.push(payload);
        },
        setPostings (state, payload) {
            state.postings = payload;
        },
        setPosting (state, payload) {
            state.posting = payload;
        },
        updatePosting (state, payload) {
            state.postings = state.postings.map(w => {
                if (w.id === payload.id) {
                    w = payload;
                }
                return w;
            })
        }
    },
    actions: {
        async createPosting ({ commit }, payload) {
            const { data } = await axios.post(`/api/v2/posting`, payload);
            commit('createPosting', data.data)
        },
        async getPostings ({ commit }) {
            const { data: { data } } = await axios.get('/api/v2/posting');
            commit('setPostings', data);
        },
        async getPosting ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/posting/${id}`);
            commit('setPosting', data);
        },
        async postingConfirm ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/posting/${id}/accept`);
            commit('updatePosting', data);
        },
        async postingDecline ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/posting/${id}/decline`);
            commit('updatePosting', data);
        },
    }
}
