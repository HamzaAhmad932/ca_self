/*
*
* These components will compile under "auth" chunck.
*
* */
const auth_base_path = "./components/general/auth";

Vue.component('login', ()=> import(auth_base_path+'/Login.vue' /* webpackChunkName: "js/auth" */));
Vue.component('register', ()=> import(auth_base_path+'/Register.vue' /* webpackChunkName: "js/auth" */));
Vue.component('forgot-password', ()=> import(auth_base_path+'/ForgotPassword.vue' /* webpackChunkName: "js/auth" */));