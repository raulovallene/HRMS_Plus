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

// appeasement-agent.ts
loadCodes(): void {
  const roleId = this.currentUser?.role || 2;
  const brandId = this.selectedBrandId || '0';

  this.api.getCodes(brandId, roleId).subscribe({
    next: (data) => {
      // si tu servicio ya devuelve el array de códigos:
      this.codes = (data as any[]).map(d => Object.assign(new Code(), d));

      // si tu servicio devuelve { status, data } entonces usa:
      // this.codes = (data.data as any[]).map(d => Object.assign(new Code(), d));
    },
    error: (err) => console.error('Error al cargar los códigos:', err)
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

    console.log('📦 Payload enviado al backend:', payload);

    this.api.addAssignedCode(payload).subscribe({
      next: (res) => {
        console.log('✅ Respuesta del servidor:', res);
        const alertBox = document.createElement('div');
        alertBox.className = 'alert alert-success text-center fw-bold';
        alertBox.innerText = `✅ Código asignado correctamente: ${this.selectedCode?.code}`;
        document.body.prepend(alertBox);
        setTimeout(() => alertBox.remove(), 5000);
      },
      error: (err) => {
        console.error('❌ Error al guardar asignación:', err);
        alert('Error al asignar código');
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
