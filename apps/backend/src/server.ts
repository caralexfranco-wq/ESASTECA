import { app } from './app.js';
import { env } from './config/env.js';
import { setupScheduler, setupWorker } from './jobs/scheduler.js';

async function bootstrap() {
  try {
    await setupScheduler();
    setupWorker();
  } catch (error) {
    console.warn('Scheduler no inicializado', error);
  }

  app.listen(env.port, () => {
    console.log(`Backend API en http://localhost:${env.port}`);
  });
}

bootstrap();
