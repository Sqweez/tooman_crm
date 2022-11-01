import BaseModal from '@/components/Modal/BaseModal';
import TCardPage from '@/components/utils/TCardPage';
import JsonExcel from "vue-json-excel";

import Vue from 'vue';

export default {
    connect () {
        Vue.component('base-modal', BaseModal);
        Vue.component('t-card-page', TCardPage)
        Vue.component('downloadExcel', JsonExcel);
    }
}

