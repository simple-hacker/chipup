import Dashboard from './views/Dashboard.vue';
import Settings from './views/Settings.vue';

const routes = [
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
    },
]

export default routes;