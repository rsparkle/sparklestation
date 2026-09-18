import { createApp } from 'vue';
import UserShell from './UserShell.vue';
import router from './router';
import '/resources/css/inventory.css'
import '/resources/css/user_characters.css'

createApp(UserShell).use(router).mount('#userApp');