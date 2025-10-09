import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { AppeasementService } from '../services/appeasement.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.html',
    styleUrls: ['./login.css']   // 👈 add this

})
export class LoginComponent implements OnInit {
  usernameForm!: FormGroup;
  passwordForm!: FormGroup;
  stage: 'username' | 'password' = 'username';
  errorMessage = '';

  constructor(
    private fb: FormBuilder,
    private api: AppeasementService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    // Handle Azure SSO callback
const tokenParam = this.route.snapshot.queryParamMap.get('token');
if (tokenParam) {
  try {
    const user = JSON.parse(atob(tokenParam)); // decode base64
    localStorage.setItem('user', JSON.stringify(user));
    console.log(user);
    this.router.navigate(['/appeasement/codes']);
    return;
  } catch (e) {
    console.error('Invalid token from SSO callback:', e);
  }
}


    // Initialize forms
    this.usernameForm = this.fb.group({
      username: ['', Validators.required]
    });

    this.passwordForm = this.fb.group({
      password: ['', Validators.required]
    });
  }

  /** First step: check username */
  onSubmit(): void {
    if (this.usernameForm.invalid) return;
    const username = this.usernameForm.value.username;

    this.api.login({ username }).subscribe({
      next: (res) => {
        if (res.status === 'sso') {
          // Redirect user to Microsoft login
          window.location.href = res.redirect;
        } else if (res.status === 'password_required') {
          this.stage = 'password';
        } else if (res.status === 'ok') {
          localStorage.setItem('user', JSON.stringify(res.user));
          this.router.navigate(['/appeasement/codes']);
        }
      },
      error: (err) => {
        console.error('Login error:', err);
        this.errorMessage = err.error?.message || 'Login failed.';
      }
    });
  }

  /** Second step: authenticate with password */
  onPasswordSubmit(): void {
  if (this.passwordForm.invalid) return;

  const payload = {
    username: this.usernameForm.value.username,
    password: this.passwordForm.value.password
  };

  this.api.login(payload).subscribe({
    next: (res) => {
      if (res.status === 'ok') {
        localStorage.setItem('user', JSON.stringify(res.user));
        this.router.navigate(['/appeasement/codes']);
      } else if (res.status === 'error') {
        this.errorMessage = res.message || 'Invalid credentials.';
      } else {
        this.errorMessage = 'Unexpected response.';
      }
    },
    error: (err) => {
      console.error('Password login error:', err);
      this.errorMessage = err.error?.message || 'Login failed.';
    }
  });
}


  resetStage(): void {
    this.stage = 'username';
    this.errorMessage = '';
    this.passwordForm.reset();
  }
}
