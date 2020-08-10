import Dashboard from './views/Dashboard.vue'
import Settings from './views/Settings.vue'
import Sessions from './views/Sessions.vue'
import LiveSession from './views/LiveSession.vue'
import Statistics from './views/Statistics.vue'
import CreateSession from './views/CreateSession.vue'
import Session from './views/Session.vue'
import NotFound from './NotFound.vue'

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
        path: '/sessions',
        name: 'sessions',
        component: Sessions,
    },
    {
        path: '/statistics',
        name: 'statistics',
        component: Statistics,
    },
    {
        path: '/session',
        name: 'session',
        component: Session,
        props: true,
    },
    {
        path: '/session/create',
        name: 'createsession',
        component: CreateSession,
    },
    {
        path: '/live',
        name: 'live',
        component: LiveSession,
    },
    {
        path: '*',
        component: NotFound
    },
]

export default routes