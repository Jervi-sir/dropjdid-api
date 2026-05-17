import { Injectable, BadRequestException } from '@nestjs/common';
import { FastifyRequest } from 'fastify';
import { randomUUID } from 'crypto';
import { createWriteStream } from 'fs';
import { mkdir, stat } from 'fs/promises';
import { extname, join } from 'path';
import { pipeline } from 'stream/promises';
import { db } from '../database/database';
import { mediaBackups } from '../database/schema';

@Injectable()
export class MediaService {
  async upload(request: FastifyRequest, destination: string) {
    const file = await request.file();

    if (!file) {
      throw new BadRequestException('No image uploaded');
    }

    const allowedMimes = [
      'image/jpg',
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/svg+xml',
      'image/webp',
      'image/heic',
      'image/heif',
    ];

    if (!allowedMimes.includes(file.mimetype)) {
      throw new BadRequestException('Invalid file type');
    }

    const allowedDestinations = [
      'conversations',
      'drops',
      'products',
      'profiles',
      'stores',
    ];

    if (!allowedDestinations.includes(destination)) {
      throw new BadRequestException('Invalid upload destination');
    }

    const directory = destination;
    const disk = 'public';

    const extension = extname(file.filename);
    const name = `${randomUUID()}${extension}`;
    const relativePath = `${directory}/${name}`;

    const uploadDir = join(
      process.env.STORAGE_PATH ?? join(process.cwd(), 'uploads'),
      directory,
    );
    const fullPath = join(uploadDir, name);

    await mkdir(uploadDir, { recursive: true });

    await pipeline(file.file, createWriteStream(fullPath));

    const publicBaseUrl = (
      process.env.STORAGE_PUBLIC_URL ??
      `${request.protocol}://${request.headers.host}/storage`
    ).replace(/\/+$/, '');

    const url = `${publicBaseUrl}/${relativePath}`;

    const fileStat = await stat(fullPath);
    const size = fileStat.size;

    const [media] = await db
      .insert(mediaBackups)
      .values({
        disk,
        directory,
        name,
        originalName: file.filename,
        mimeType: file.mimetype,
        size,
        path: relativePath,
        url,
        collection: 'default',
      })
      .returning();

    return media;
  }
}
