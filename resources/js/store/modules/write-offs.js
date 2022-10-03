import axios from 'axios';
import axiosClient from '@/utils/axiosClient';

export default {
    state: {
        writeOffs: [],
        writeOff: null
    },
    getters: {
        WRITE_OFFS: state => state.writeOffs,
        WRITE_OFF: state => state.writeOff
    },
    mutations: {
        createWriteOff (state, payload) {
            state.writeOffs.push(payload);
        },
        setWriteOffs (state, payload) {
            state.writeOffs = payload;
        },
        setWriteOff (state, payload) {
            state.writeOff = payload;
        },
        updateWriteOff (state, payload) {
            state.writeOffs = state.writeOffs.map(w => {
                if (w.id === payload.id) {
                    w = payload;
                }
                return w;
            })
        }
    },
    actions: {
        async createWriteOff ({ commit }, payload) {
            const { data } = await axios.post(`/api/v2/write-offs`, payload);
            commit('createWriteOff', data.data)
        },
        async getWriteOffs ({ commit }) {
            const { data: { data } } = await axios.get('/api/v2/write-offs');
            commit('setWriteOffs', data);
        },
        async getWriteOff ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/write-offs/${id}`);
            commit('setWriteOff', data);
        },
        async writeOffConfirm ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/write-offs/${id}/accept`);
            commit('updateWriteOff', data);
        },
        async writeOffDecline ({ commit }, id) {
            const { data: { data } } = await axios.get(`/api/v2/write-offs/${id}/decline`);
            commit('updateWriteOff', data);
        },
    }
}
