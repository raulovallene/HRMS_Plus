import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-team',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './team.html',
  styleUrls: ['./team.css']
})
export class Team {
  // ===================
  // Sample data
  // ===================
  clients: string[] = ['Client A', 'Client B', 'Client C', 'Client D'];

  allocations: { client: string, percent: number }[] = [];

  // Employees pending approval
  pendingEmployees = [
    { name: 'Susan Lee', email: 'slee@example.com', id: 'EMP2001', department: 'Marketing', title: 'Coordinator' },
    { name: 'David Johnson', email: 'djohnson@example.com', id: 'EMP2002', department: 'Sales', title: 'Sales Rep' },
    { name: 'Luis Martínez', email: 'lmartinez@example.com', id: 'EMP2003', department: 'IT', title: 'Support Tech' }
  ];

  // ===================
  // Selection state
  // ===================
  selectedClient: string | null = null;
  selectedAllocation: { client: string, percent: number } | null = null;
  allocationPercent: number = 0;

  // Multi-selection for Pending Approvals
  selectedPending: any[] = [];

  // ===================
  // Modal Controls
  // ===================
  openAllocationModal() {
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

  // ===================
  // Table Selections
  // ===================
  selectClient(client: string) {
    this.selectedClient = client;
    this.selectedAllocation = null;
  }

  selectAllocation(allocation: { client: string; percent: number }) {
    this.selectedAllocation = allocation;
    this.selectedClient = null;
  }

  // Toggle multiple selections in Pending Approvals
  togglePendingSelection(employee: any) {
    const idx = this.selectedPending.indexOf(employee);
    if (idx >= 0) {
      this.selectedPending.splice(idx, 1); // remove if already selected
    } else {
      this.selectedPending.push(employee); // add if not selected
    }
  }

  // ===================
  // Actions
  // ===================
  saveAllocation() {
    if (this.selectedClient && this.allocationPercent > 0) {
      const existingIndex = this.allocations.findIndex(
        (a) => a.client === this.selectedClient
      );

      if (existingIndex >= 0) {
        this.allocations[existingIndex].percent = this.allocationPercent;
      } else {
        this.allocations.push({
          client: this.selectedClient,
          percent: this.allocationPercent,
        });
      }

      this.allocationPercent = 0;
      this.selectedClient = null;
    }
  }

  removeAllocation() {
    if (this.selectedAllocation) {
      this.allocations = this.allocations.filter(
        (a) => a !== this.selectedAllocation
      );
      this.selectedAllocation = null;
    }
  }

  // Approve multiple pending employees
  approveSelected() {
    if (this.selectedPending.length > 0) {
      // por ahora solo los mostramos en consola
      console.log('Approved employees:', this.selectedPending);

      // luego puedes moverlos a Current Team
      this.selectedPending = [];
    }
  }

  // ===================
  // Computed
  // ===================
  get totalAllocated(): number {
    return this.allocations.reduce((sum, a) => sum + a.percent, 0);
  }
}
