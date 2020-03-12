import Dashboard from './views/Dashboard.vue';
import Settings from './views/Settings.vue';
import Bankroll from './views/Bankroll.vue';
import Stats from './views/Stats.vue';
import Session from './views/Session.vue';

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
    {
        path: '/bankroll',
        name: 'bankroll',
        component: Bankroll,
    },
    {
        path: '/stats',
        name: 'stats',
        component: Stats,
    },
    {
        path: '/session',
        name: 'session',
        component: Session,
    },
]

export default routes;