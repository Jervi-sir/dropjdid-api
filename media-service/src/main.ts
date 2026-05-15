import { NestFactory } from '@nestjs/core';
import {
  FastifyAdapter,
  NestFastifyApplication,
} from '@nestjs/platform-fastify';
import multipart from '@fastify/multipart';
import { AppModule } from './app.module';
import fastifyStatic from '@fastify/static';
import { join } from 'path';

async function bootstrap() {
  const app = await NestFactory.create<NestFastifyApplication>(
    AppModule,
    new FastifyAdapter(),
  );

  await app.register(multipart);

  await app.register(fastifyStatic, {
    root: join(process.cwd(), 'uploads'),
    prefix: '/storage/',
  });

  app.enableCors();

  await app.listen(process.env.PORT ?? 8001, '0.0.0.0');
}

bootstrap();
