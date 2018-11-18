import axios from 'axios'
import {Message, MessageBox} from 'element-ui'
import {store} from '../resources'

// create an axios instance
const service = axios.create({baseURL: process.env.BASE_API, timeout: 6000})

service.defaults.retry = 2;
service.defaults.retryDelay = 1000;

// request interceptor
service.interceptors.request.use(
    config => {
        if (store.getters.token) config.headers['X-Token'] = store.getters.token;
        return config;
    },
    error => {
        Promise.reject(error);
    }
)

// response interceptor
service.interceptors.response.use(
    // response => response,
    response => {
        const res = response.data;
        // 1010:非法的token
        if (res.code === 1010) {
            MessageBox.confirm('You have been logged out.', 'Warning', {
                confirmButtonText: 'ReLogin',
                cancelButtonText: 'Cancel',
                type: 'warning'
            }).then(() => {
                store.dispatch('FedLogOut').then(() => {location.reload();})
            })
            return Promise.reject(new Error('Error Token,Please login again.'));
        }

        if (res.code == 1000) return response.data;

        return Promise.reject(new Error(res.data));
    },
    error => {
        var config = error.config;
        if(!config || !config.retry) return Promise.reject(error);

        config.retryCount = config.retryCount || 0;
        if(config.retryCount >= config.retry) return Promise.reject(new Error('Bad network,request timeout!'));
        config.retryCount += 1;

        var backoff = new Promise(function(resolve) {
            setTimeout(function() {resolve();}, config.retryDelay || 1);
        });

        return backoff.then(function() {
            return service(config);
        });
    }
)

export default service
