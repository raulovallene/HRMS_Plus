import { Component, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-team',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './team.html',
  styleUrls: ['./team.css']
})
export class Team implements AfterViewInit {
  clients: string[] = ['ColeHaan', 'RAG and BONE', 'SEA', 'Arbonne', 'Moder', 'SEAU', 'Hugo BOSS', 'Marc Jacobs', 'Haggar', 'THD', 'Aucera'];
  allocations: { client: string, percent: number }[] = [];

  // Current Team Data
  currentTeam = [
    { name: 'Jane Smith', email: 'jane@example.com', id: 'EMP1002', department: 'Finance', title: 'Accountant', status: 'Completed' },
    { name: 'Charlie Branth', email: 'cbranth@example.com', id: 'EMP1003', department: 'Production', title: 'Customer Care Agent', status: 'Pending' },
    { name: 'Robert Brown', email: 'robert@example.com', id: 'EMP1004', department: 'Engineering', title: 'Developer', status: 'Overdue' }
  ];

  // Pending Approvals
  pendingEmployees = [
    { name: 'Susan Lee', email: 'slee@example.com', id: 'EMP2001', department: 'Marketing', title: 'Coordinator' },
    { name: 'David Johnson', email: 'djohnson@example.com', id: 'EMP2002', department: 'Sales', title: 'Sales Rep' },
    { name: 'Luis Martínez', email: 'lmartinez@example.com', id: 'EMP2003', department: 'IT', title: 'Support Tech' }
  ];

  selectedClient: string | null = null;
  selectedAllocation: { client: string, percent: number } | null = null;
  allocationPercent: number = 0;

  selectedPending: any[] = [];
  selectedCurrent: any[] = [];

  // ======================
  // Modal Controls
  // ======================
  openAllocationModal(member?: any) {
    const modal = new (window as any).bootstrap.Modal(
      document.getElementById('allocationModal')
    );
    modal.show();
  }

  openAllocationInput() {
    if (!this.selectedClient) return;
    const modal = new (window as any).bootstrap.Modal(
      document.getElementById('allocationInputModal')
    );
    modal.show();
  }

  // ======================
  // Table Selections
  // ======================
  toggleCurrentSelection(member: any) {
    const idx = this.selectedCurrent.indexOf(member);
    if (idx >= 0) {
      this.selectedCurrent.splice(idx, 1);
    } else {
      this.selectedCurrent.push(member);
    }
  }

  togglePendingSelection(employee: any) {
    const idx = this.selectedPending.indexOf(employee);
    if (idx >= 0) {
      this.selectedPending.splice(idx, 1);
    } else {
      this.selectedPending.push(employee);
    }
  }

  selectClient(client: string) {
    this.selectedClient = client;
    this.selectedAllocation = null;
  }

  selectAllocation(allocation: { client: string; percent: number }) {
    this.selectedAllocation = allocation;
    this.selectedClient = null;
  }

  saveAllocation() {
    if (this.selectedClient && this.allocationPercent > 0) {
      const existingIndex = this.allocations.findIndex(a => a.client === this.selectedClient);
      if (existingIndex >= 0) {
        this.allocations[existingIndex].percent = this.allocationPercent;
      } else {
        this.allocations.push({ client: this.selectedClient, percent: this.allocationPercent });
      }
      this.allocationPercent = 0;
      this.selectedClient = null;
    }
  }

  removeAllocation() {
    if (this.selectedAllocation) {
      this.allocations = this.allocations.filter(a => a !== this.selectedAllocation);
      this.selectedAllocation = null;
    }
  }

  approveSelected() {
    if (this.selectedPending.length > 0) {
      this.selectedPending = [];
    }
  }

  get totalAllocated(): number {
    return this.allocations.reduce((sum, a) => sum + a.percent, 0);
  }

  ngAfterViewInit(): void {
    if ((window as any).bootstrap) {
      const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach((tooltipTriggerEl) => {
        new (window as any).bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
  }
}
