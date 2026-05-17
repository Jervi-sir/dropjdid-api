import { defineConfig } from 'drizzle-kit';
import { loadEnv } from './src/env/load-env';

loadEnv();

export default defineConfig({
  schema: './src/database/schema.ts',
  out: './drizzle',
  dialect: 'postgresql',
  dbCredentials: {
    url: process.env.DATABASE_URL!,
  },
});
