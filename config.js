// Configuration for ManifestLink deployment
// This file will be used to manage different environments

const config = {
    // Development (local XAMPP)
    development: {
        apiBaseUrl: 'http://localhost/manifestlink',
        database: {
            host: 'localhost',
            port: 3307,
            name: 'manifestlink'
        }
    },
    
    // Production (when deployed)
    production: {
        // Update this URL after deploying your PHP backend
        apiBaseUrl: 'https://your-backend-url.herokuapp.com',
        database: {
            // These will be set by your hosting provider
            host: 'your-database-host',
            port: 3306,
            name: 'manifestlink'
        }
    }
};

// Get current environment
const environment = window.location.hostname === 'localhost' ? 'development' : 'production';

// Export current config
window.appConfig = config[environment];

// Helper function to get API URL
window.getApiUrl = function(endpoint) {
    return `${window.appConfig.apiBaseUrl}/${endpoint}`;
};

console.log('ManifestLink Config Loaded:', window.appConfig);
