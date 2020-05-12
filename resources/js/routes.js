import Dashboard from './views/Dashboard.vue'
import Settings from './views/Settings.vue'
import Sessions from './views/Sessions.vue'
import LiveSession from './views/LiveSession.vue'
import Statistics from './views/Statistics.vue'
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
        path: '/live',
        name: 'live',
        component: LiveSession,
    },
    {
        path: '/whoops',
        name: 'NotFound',
        component: NotFound
    },
    {
        path: '*',
        redirect: '/whoops'
    },
]

export default routes