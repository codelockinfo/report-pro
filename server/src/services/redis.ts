import Redis from 'ioredis';
import dotenv from 'dotenv';

dotenv.config();

let redisClient: Redis;

export function getRedisClient(): Redis {
  if (!redisClient) {
    redisClient = new Redis({
      host: process.env.REDIS_HOST || 'localhost',
      port: Number(process.env.REDIS_PORT) || 6379,
      password: process.env.REDIS_PASSWORD || undefined,
      retryStrategy: (times) => {
        const delay = Math.min(times * 50, 2000);
        return delay;
      },
    });
  }
  return redisClient;
}

export async function initializeRedis() {
  const client = getRedisClient();
  await client.ping();
}

export async function cacheGet(key: string): Promise<string | null> {
  const client = getRedisClient();
  return await client.get(key);
}

export async function cacheSet(key: string, value: string, ttlSeconds?: number): Promise<void> {
  const client = getRedisClient();
  if (ttlSeconds) {
    await client.setex(key, ttlSeconds, value);
  } else {
    await client.set(key, value);
  }
}

export async function cacheDelete(key: string): Promise<void> {
  const client = getRedisClient();
  await client.del(key);
}

