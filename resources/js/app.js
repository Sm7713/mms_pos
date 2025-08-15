require('./bootstrap');

import { createApp } from 'vue';

import App from './vue/app.vue'
import about from './vue/about.vue'

// const app =new Vue({
//     el:'#app',
//     components:{App}
// });

const app = createApp({
    el:'#app',
    components:{App,about}
}).mount('#app');

// app.component('app',App);
// app.mount('#app');
