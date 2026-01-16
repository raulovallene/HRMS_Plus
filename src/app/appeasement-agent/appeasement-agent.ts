import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AppeasementService } from '../services/appeasement.service';
import { Code } from '../models/code';

declare const bootstrap: any;

@Component({
  selector: 'app-appeasement-agent',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './appeasement-agent.html',
  styleUrls: ['./appeasement-agent.css']
})
export class AppeasementAgent implements OnInit {
  /** All codes retrieved from API */
  codes: Code[] = [];

  /** Filtered codes (visible in table) */
  filteredCodes: Code[] = [];

  /** Reactive form for assignment */
  assignForm!: FormGroup;

  /** Selected code for modal */
  selectedCode: Code | null = null;

  /** Current user */
  currentUser: any = JSON.parse(localStorage.getItem('user') || '{}');

  /** Current date (UTC-based) */
  readonly today = new Date().toISOString().split('T')[0];

  /** User brands */
  userBrands: { id: number; name: string }[] = [];

  /** Brand and search filter */
  selectedBrandId: string = '';
  searchTerm: string = '';

  constructor(private fb: FormBuilder, private api: AppeasementService) {}

  ngOnInit(): void {
    this.assignForm = this.fb.group({
      caseClient: ['', Validators.required],
      description: ['', Validators.required],
      user: [{ value: this.currentUser?.username || '', disabled: true }],
      date: [{ value: this.today, disabled: true }]
    });

    this.userBrands = this.currentUser?.brands || [];
    if (this.userBrands.length > 0) {
      this.selectedBrandId = String(this.userBrands[0].id);
    }

    this.loadCodes();
  }

  /** Loads codes from API */
  loadCodes(): void {
    const roleId = this.currentUser?.role || 2;
    const brandId = this.selectedBrandId || '0';

    this.api.getCodes(brandId, roleId).subscribe({
      next: (data) => {
        this.codes = (data as any[]).map(d => Object.assign(new Code(), d));
        this.filteredCodes = [...this.codes];
      },
      error: (err) => console.error('❌ Error loading codes:', err)
    });
  }

  /** Filters the codes based on search text and brand */
  applyFilters(): void {
    const search = this.searchTerm.toLowerCase().trim();

    this.filteredCodes = this.codes.filter(c => {
      const matchesBrand = !this.selectedBrandId || c.idBrand === +this.selectedBrandId;
      const matchesText =
        !search ||
        c.description.toLowerCase().includes(search) ||
        c.brandName?.toLowerCase().includes(search);
      return matchesBrand && matchesText;
    });
  }

  /** Brand dropdown changed */
  onBrandChange(event: any): void {
    this.selectedBrandId = event.target.value;
    this.applyFilters();
  }

  /** Search input changed */
  onSearchChange(event: any): void {
    this.searchTerm = event.target.value;
    this.applyFilters();
  }

  /** Opens the assign modal */
  openModal(code: Code): void {
    this.selectedCode = code;

    this.assignForm.reset({
      caseClient: '',
      description: '',
      user: this.currentUser?.username || '',
      date: this.today
    });

    const modalEl = document.getElementById('assignCodeModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  /** Saves the assignment */
  saveAssignCode(): void {
  if (this.assignForm.invalid || !this.selectedCode) return;

  const payload = {
    ...this.assignForm.getRawValue(),
    codeId: this.selectedCode.idcodes,
    idUser: this.currentUser?.idUser
  };

  this.api.addAssignedCode(payload).subscribe({
    next: (res) => {

      // ✅ Create alert with copy button
      const alertBox = document.createElement('div');
      alertBox.className =
        'alert alert-success text-center fw-bold d-flex justify-content-between align-items-center';
      alertBox.style.position = 'fixed';
      alertBox.style.top = '10px';
      alertBox.style.left = '50%';
      alertBox.style.transform = 'translateX(-50%)';
      alertBox.style.zIndex = '2000';
      alertBox.style.minWidth = '380px';
      alertBox.style.padding = '10px 20px';
      alertBox.style.borderRadius = '10px';
      alertBox.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';

      // Insert code text and button
      alertBox.innerHTML = `
        <span>Code assigned successfully: <strong>${this.selectedCode?.code}</strong></span>
        <button id="copyBtn" class="btn btn-sm btn-outline-light ms-3" title="Copy code to clipboard">
          <i class="bi bi-x-lg"></i>
        </button>
      `;

      document.body.prepend(alertBox);

      // ✅ Copy handler — copies the real code, not the alert text
      const copyBtn = alertBox.querySelector('#copyBtn') as HTMLButtonElement;
      copyBtn.addEventListener('click', async () => {
        const codeToCopy = this.selectedCode?.code || '';
        try {
          await navigator.clipboard.writeText(codeToCopy);
          // Visual feedback
          copyBtn.innerHTML = '<i class="bi bi-check2"></i>';
          copyBtn.classList.remove('btn-outline-light');
          copyBtn.classList.add('btn-light', 'text-success');
          // Remove alert after success
          setTimeout(() => alertBox.remove(), 1000);
        } catch (err) {
          console.error('❌ Clipboard copy failed:', err);
          alert('Failed to copy code to clipboard.');
        }
      });
    },
    error: (err) => {
      console.error('❌ Error during code assignment:', err);
      alert('Oops! Something went wrong.');
    },
    complete: () => {
      const modalEl = document.getElementById('assignCodeModal');
      if (modalEl) {
        const modal =
          bootstrap.Modal.getInstance(modalEl) ?? new bootstrap.Modal(modalEl);
        modal.hide();
      }

      this.assignForm.reset({
        caseClient: '',
        description: '',
        user: this.currentUser?.username || '',
        date: this.today
      });
      this.selectedCode = null;

      this.loadCodes();
    }
  });
}
}
