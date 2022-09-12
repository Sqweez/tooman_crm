import axios from 'axios';

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
        }
    },
    actions: {
        async createWriteOff ({ commit }, payload) {
            const { data } = await axios.post(`/api/v2/write-offs`, payload);
            commit('createWriteOff', {})
        }
    }
}
