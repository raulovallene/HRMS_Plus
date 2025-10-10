import { Component } from '@angular/core';
import { AppeasementService } from '../services/appeasement.service';

@Component({
  selector: 'app-profile',
  imports: [],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile {

  profilePhotoUrl = 'https://cdn-icons-png.flaticon.com/512/847/847969.png';

  constructor(private api: AppeasementService) {}

  ngOnInit(): void {
    // Check cached photo first
    const cached = localStorage.getItem('profile_photo_url');
    if (cached) {
      this.profilePhotoUrl = cached;
      return;
    }

    // Otherwise, fetch from Microsoft Graph
    this.api.getMicrosoftProfilePhoto().subscribe((photoUrl) => {
      if (photoUrl) {
        this.profilePhotoUrl = photoUrl;
        localStorage.setItem('profile_photo_url', photoUrl);
      } else {
        console.warn('No Microsoft photo found — using fallback icon.');
      }
    });
  }

}
