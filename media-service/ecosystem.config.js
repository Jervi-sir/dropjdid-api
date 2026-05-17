module.exports = {
  apps: [
    {
      name: 'dropjdid:media-service',
      script: 'dist/src/main.js',
      cwd: '/home/jervi/projects/dropjdid-api/media-service',
      instances: 1,
      exec_mode: 'fork',
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: '18001',
      },
    },
  ],
};
