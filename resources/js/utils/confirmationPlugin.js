import GlobalConfirmationModal from '@/components/Modal/GlobalConfirmationModal';
import store from '@/store';
import vuetify from '@/plugins/vuetify';
import PromptModal from '@/components/Modal/PromptModal';

export default {
    install (Vue) {
        Vue.prototype.$confirm = (text = 'Подтвердите действие', title = 'Подтвердите действие', options = {}) => {
            return new Promise((resolve, reject) => {
                try {
                    let state = true;
                    const props = {
                        title, text, options, state,
                    };
                    const on = {};
                    const comp = new Vue({
                        store,
                        vuetify,
                        render: (h) => h(GlobalConfirmationModal, {props, on}),
                    });

                    on.confirmed = (val) => {
                        if (val) {
                            resolve(val);
                        } else {
                            reject(false);
                        }
                        window.setTimeout(() => comp.$destroy(), 200);
                    };

                    comp.$mount();
                    document.getElementById('app').appendChild(comp.$el);
                } catch (err) {
                    reject(err);
                }
            });
        };
        Vue.prototype.$prompt = (label = 'Название', title = 'Введите название', options = {}) => {
            return new Promise((resolve, reject) => {
                try {
                    let state = true;
                    const props = {
                        title, label, options, state,
                    };
                    const on = {};
                    const comp = new Vue({
                        store,
                        vuetify,
                        render: (h) => h(PromptModal, {props, on}),
                    });

                    on.confirmed = (val) => {
                        if (val) {
                            resolve(val);
                        } else {
                            reject(false);
                        }
                        window.setTimeout(() => comp.$destroy(), 200);
                    };

                    comp.$mount();
                    document.getElementById('app').appendChild(comp.$el);
                } catch (err) {
                    reject(err);
                }
            });
        };
    }
}
