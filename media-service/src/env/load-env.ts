import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';

export function loadEnv() {
  const envPath = resolve(process.cwd(), '.env');

  if (!existsSync(envPath)) {
    return;
  }

  const envFile = readFileSync(envPath, 'utf8');

  for (const line of envFile.split(/\r?\n/)) {
    const trimmedLine = line.trim();

    if (!trimmedLine || trimmedLine.startsWith('#')) {
      continue;
    }

    const separatorIndex = trimmedLine.indexOf('=');

    if (separatorIndex === -1) {
      continue;
    }

    const key = trimmedLine.slice(0, separatorIndex).trim();
    const rawValue = trimmedLine.slice(separatorIndex + 1).trim();
    const quotedValue = rawValue.match(/^([\"'])(.*)\1$/);
    const value = quotedValue ? quotedValue[2] : rawValue;

    if (!(key in process.env)) {
      process.env[key] = value;
    }
  }
}
