import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { 
    faEdit, faTrashCan, faEnvelope 
} from '@fortawesome/free-regular-svg-icons';

import { 
    faHome, faUser, faSitemap, faFileLines, faLayerGroup, 
    faMoneyBillWave, faMoneyBillTrendUp, faPercent, faSuitcase, 
    faChevronDown, faFileCirclePlus, faBuilding, faFileZipper, 
    faWallet, faSquareCheck, faTrash, faPaperclip, faEye, faEyeSlash, 
    faChevronUp, faSpinner, faEdit as faEditSolid, faTrashCan as faTrashCanSolid,
    faTriangleExclamation
} from '@fortawesome/free-solid-svg-icons';

library.add(
    faEdit, faTrashCan, faEnvelope, // Regular icons
    faHome, faUser, faSitemap, faFileLines, faLayerGroup, 
    faMoneyBillWave, faMoneyBillTrendUp, faPercent, faSuitcase, 
    faChevronDown, faFileCirclePlus, faBuilding, faFileZipper, 
    faWallet, faSquareCheck, faTrash, faPaperclip, faEye, faEyeSlash, 
    faChevronUp, faSpinner, faEditSolid, faTrashCanSolid, faTriangleExclamation,

);

const appName = import.meta.env.VITE_APP_NAME || 'MONALISA';

createInertiaApp({
    title: (title) => `${appName} - ${title}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
           .use(ZiggyVue)
           .component('font-awesome-icon', FontAwesomeIcon)
           .mount(el);
    },
    progress: false,
});