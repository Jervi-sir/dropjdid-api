module.exports = {
  apps: [
    {
      name: 'dropjdid:laravel-api',
      script: 'artisan',
      interpreter: 'php',
      args: 'serve --host=127.0.0.1 --port=18000',
      cwd: '/home/jervi/projects/dropjdid-api/laravel',
      instances: 1,
      exec_mode: 'fork',
      watch: false,
      max_memory_restart: '512M',
      env: {
        APP_ENV: 'production',
      },
    },
    {
      name: 'dropjdid:laravel-worker',
      script: 'artisan',
      interpreter: 'php',
      args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
      cwd: '/home/jervi/projects/dropjdid-api/laravel',
      instances: 1,
      exec_mode: 'fork',
      watch: false,
      max_memory_restart: '512M',
      env: {
        APP_ENV: 'production',
      },
    },
  ],
};
