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
  codes: Code[] = [];
  assignForm!: FormGroup;
  selectedCode: Code | null = null;
  currentUser: any = JSON.parse(localStorage.getItem('user') || '{}');
  readonly today = new Date().toISOString().split('T')[0];
  userBrands: { id: number; name: string }[] = [];
  selectedBrandId: string = '';

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

  loadCodes(): void {
    const roleId = this.currentUser?.role || 2;
    const brandId = this.selectedBrandId || '0';

    this.api.getCodes(brandId, roleId).subscribe({
      next: (data) => {
        this.codes = (data as any[]).map(d => Object.assign(new Code(), d));
      },
      error: (err) => console.error('Oops! something went wrong at loading the codes...', err)
    });
  }

  onBrandChange(event: any): void {
    this.selectedBrandId = event.target.value;
    this.loadCodes();
  }

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

  saveAssignCode(): void {
    if (this.assignForm.invalid || !this.selectedCode) return;

    const payload = {
      ...this.assignForm.getRawValue(),
      codeId: this.selectedCode.idcodes,
      idUser: this.currentUser?.idUser
    };

    console.log('Payload sent to backend:', payload);

    this.api.addAssignedCode(payload).subscribe({
      next: (res) => {
        console.log('✅ Server response:', res);

        // ✅ Create alert with copy button
        const alertBox = document.createElement('div');
        alertBox.className = 'alert alert-success text-center fw-bold d-flex justify-content-between align-items-center';
        alertBox.style.position = 'fixed';
        alertBox.style.top = '10px';
        alertBox.style.left = '50%';
        alertBox.style.transform = 'translateX(-50%)';
        alertBox.style.zIndex = '2000';
        alertBox.style.width = 'fit-content';
        alertBox.style.minWidth = '300px';
        alertBox.style.padding = '10px 20px';
        alertBox.style.borderRadius = '10px';
        alertBox.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
        alertBox.innerHTML = `
          <span>✅ Código asignado correctamente: <strong>${this.selectedCode?.code}</strong></span>
          <button id="copyBtn" class="btn btn-sm btn-outline-light ms-3" title="Copiar código">
            <i class="bi bi-clipboard"></i>
          </button>
        `;

        document.body.prepend(alertBox);

        // ✅ Add click listener to copy the code and close alert
        const copyBtn = alertBox.querySelector('#copyBtn') as HTMLButtonElement;
        copyBtn.addEventListener('click', async () => {
          try {
            await navigator.clipboard.writeText(this.selectedCode?.code || '');
            copyBtn.innerHTML = '<i class="bi bi-check2"></i>';
            copyBtn.classList.remove('btn-outline-light');
            copyBtn.classList.add('btn-light', 'text-success');
            setTimeout(() => alertBox.remove(), 1000); // remove after 1s
          } catch (err) {
            console.error('❌ Error copying to clipboard:', err);
          }
        });
      },
      error: (err) => {
        console.error('❌ Error doing the assignation', err);
        alert('Ooops! Something went wrong');
      },
      complete: () => {
        const modalEl = document.getElementById('assignCodeModal');
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl) ?? new bootstrap.Modal(modalEl);
          modal.hide();
        }

        this.assignForm.reset({
          caseClient: '',
          description: '',
          user: this.currentUser?.username || '',
          date: this.today
        });
        this.selectedCode = null;
      }
    });
  }
}
