import axios from 'axios';

const apiInstance = axios.create({
    baseURL: '/api/v1'
});

//validate response
apiInstance.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response.status === 401) {
        localStorage.removeItem('sf_user');

        return window.location.href = '/home';
    }
    return Promise.reject(error);
});

export default apiInstance;
