import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensure axios sends Laravel's CSRF token for all requests when present in the page
const token = typeof document !== 'undefined' && document.head && document.head.querySelector ? document.head.querySelector('meta[name="csrf-token"]') : null;
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}
