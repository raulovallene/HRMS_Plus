import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AppeasementService } from '../services/appeasement.service';

@Component({
  selector: 'app-side-bar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './side-bar.html',
  styleUrls: ['./side-bar.css']
})
export class SideBar {
  constructor(private api: AppeasementService) {}

  logout(): void {
    if (confirm('Are you sure you want to log out?')) {
      this.api.logout();
    }
  }
}
