process.env.CHROME_BIN = '/usr/bin/chromium';

module.exports = function (config) {
  config.set({
    basePath: '',
    logLevel: config.LOG_INFO,
    autoWatch: true,
    clearContext: true,
    frameworks: ['jasmine', '@angular-devkit/build-angular'],
    plugins: [
      require('karma-jasmine'),
      require('karma-chrome-launcher'),
      require('karma-jasmine-html-reporter'),
      require('karma-coverage'),
      require('karma-spec-reporter'),
    ],
    browsers: ['ChromeHeadlessNoSandbox'],
    customLaunchers: {
      ChromeHeadlessNoSandbox: {
        base: 'ChromeHeadless',
        flags: ['--no-sandbox', '--disable-web-security', '--disable-dev-shm-usage'],
      },
    },
    restartOnFileChange: true,
    reporters: ['kjhtml', 'spec'],
    specReporter: {
      maxLogLines: 5,
      suppressErrorSummary: false,
      suppressFailed: false,
      suppressPassed: false,
      suppressSkipped: true,
      showSpecTiming: false,
      failFast: false,
    },
    // Force to use 'polling' to fix problems with docker and watch files
    customHeaders: [
      {
        name: 'Access-Control-Allow-Origin',
        value: '*',
      },
    ],
    watcher: {
      usePolling: true,
      interval: 1000,
    },
  });
};
