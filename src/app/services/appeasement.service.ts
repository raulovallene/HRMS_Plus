import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { Code } from '../models/code';

@Injectable({ providedIn: 'root' })
export class AppeasementService {
  private readonly API_URL = 'https://my.cxperts.us/api/';

  constructor(private http: HttpClient) {}

  /** ===============================
   *  🔹 Codes Methods
   *  =============================== */
  getCodes(brandId: string, roleId: number): Observable<Code[]> {
    return this.http
      .get<any>(`${this.API_URL}/codes.php?brandId=${brandId}&roleId=${roleId}`)
      .pipe(map((res) => (res.status === 'ok' ? res.data : [])));
  }

  addAssignedCode(payload: any): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/assignedcodes.php`, payload);
  }

  /** ===============================
   *  🔹 Authentication Methods
   *  =============================== */

  /**
   * Step 1: Check username to determine if SSO or password login
   */
  checkUser(username: string): Observable<any> {
    const payload = { username };
    return this.http.post<any>(`${this.API_URL}/login.php`, payload);
  }

  /**
   * Step 2: Perform username + password authentication
   */
  loginUser(username: string, password: string): Observable<any> {
    const payload = { username, password };
    return this.http.post<any>(`${this.API_URL}/login.php`, payload);
  }

  login(payload: any) {
  return this.http.post<any>('http://localhost/endpoints/login.php', payload);
}

logout(): void {
  localStorage.removeItem('user');
  window.location.href = '/login';
}


}
