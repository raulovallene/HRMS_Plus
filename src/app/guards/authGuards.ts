import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

/**
 * AuthGuard — prevents access to protected routes if user is not logged in.
 */
export const AuthGuards: CanActivateFn = () => {
  const router = inject(Router);

  const user = localStorage.getItem('user');
  if (user) {
    return true;
  }

  // No user? Redirect to login
  router.navigate(['/login']);
  return false;
};
