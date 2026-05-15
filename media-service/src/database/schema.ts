import {
  bigint,
  bigserial,
  integer,
  pgTable,
  timestamp,
  varchar,
} from 'drizzle-orm/pg-core';

export const mediaBackups = pgTable('media_backups', {
  id: bigserial('id', { mode: 'number' }).primaryKey(),

  disk: varchar('disk', { length: 255 }).default('public').notNull(),
  directory: varchar('directory', { length: 255 }),

  name: varchar('name', { length: 255 }),
  originalName: varchar('original_name', { length: 255 }),
  mimeType: varchar('mime_type', { length: 255 }),
  size: bigint('size', { mode: 'number' }),

  path: varchar('path', { length: 255 }),
  url: varchar('url', { length: 255 }).notNull(),

  collection: varchar('collection', { length: 255 })
    .default('default')
    .notNull(),

  mediableType: varchar('mediable_type', { length: 255 }),
  mediableId: bigint('mediable_id', { mode: 'number' }),

  createdAt: timestamp('created_at', { mode: 'date' }).defaultNow().notNull(),
  updatedAt: timestamp('updated_at', { mode: 'date' }).defaultNow().notNull(),
});
