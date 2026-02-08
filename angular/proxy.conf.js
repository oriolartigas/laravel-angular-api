const PROXY_CONFIG = {
  '/api': {
    target: 'http://laravel-service:8080',
    secure: false,
    changeOrigin: true,
    logLevel: 'debug',
  },
};

export default PROXY_CONFIG;
