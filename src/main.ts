import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { App } from './app/app';           // 👈 cambia este nombre al real
import { routes } from './app/app.routes'; // 👈 cambia appRoutes → routes

bootstrapApplication(App, {
  providers: [
    provideRouter(routes),
    provideHttpClient(withInterceptors([]))
  ]
}).catch(err => console.error(err));
