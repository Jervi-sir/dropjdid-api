import { Controller, Param, Post, Req } from '@nestjs/common';
import { MediaService } from './media.service';
import type { FastifyRequest } from 'fastify';

@Controller('media')
export class MediaController {
  constructor(private readonly mediaService: MediaService) {}

  @Post('upload/:destination')
  async upload(
    @Param('destination') destination: string,
    @Req() request: FastifyRequest,
  ) {
    return this.mediaService.upload(request, destination);
  }
}
