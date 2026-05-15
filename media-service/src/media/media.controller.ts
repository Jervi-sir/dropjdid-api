import { Controller, Post, Req } from '@nestjs/common';
import { MediaService } from './media.service';
import type { FastifyRequest } from 'fastify';

@Controller('media')
export class MediaController {
  constructor(private readonly mediaService: MediaService) {}

  @Post('upload')
  async upload(@Req() request: FastifyRequest) {
    return this.mediaService.upload(request);
  }
}
